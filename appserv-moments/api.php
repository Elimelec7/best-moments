<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper para slug
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'evento-' . time() : $text;
}

// Obtener URL base
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    return rtrim($protocol . $host . $scriptDir, '/');
}

// 1. OBTENER LISTA DE EVENTOS (Ocultando el PIN de seguridad)
if ($action === 'get_events') {
    $stmt = $pdo->query("
        SELECT e.id, e.code, e.title, e.description, e.event_date, e.location, e.theme, e.created_at, COUNT(m.id) as media_count 
        FROM events e 
        LEFT JOIN media m ON e.id = m.event_id 
        GROUP BY e.id 
        ORDER BY e.created_at DESC 
        LIMIT 20
    ");
    $events = $stmt->fetchAll();
    echo json_encode(['events' => $events]);
    exit;
}

// VERIFICAR PIN DE ACCESO
if ($action === 'verify_pin') {
    $code = $_GET['code'] ?? $_POST['code'] ?? '';
    $pin = trim($_GET['pin'] ?? $_POST['pin'] ?? '');

    $stmt = $pdo->prepare("SELECT admin_pin FROM events WHERE code = ?");
    $stmt->execute([$code]);
    $ev = $stmt->fetch();

    if (!$ev) {
        http_response_code(404);
        echo json_encode(['valid' => false, 'error' => 'Evento no encontrado']);
        exit;
    }

    $valid = ($pin === $ev['admin_pin']);
    echo json_encode(['valid' => $valid]);
    exit;
}

// 2. CREAR EVENTO
if ($action === 'create_event') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $title = trim($data['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['error' => 'El título del evento es obligatorio']);
        exit;
    }

    $customCode = trim($data['custom_code'] ?? '');
    $baseCode = !empty($customCode) ? slugify($customCode) : slugify($title);
    
    // Garantizar código único
    $code = $baseCode;
    $counter = 1;
    while (true) {
        $check = $pdo->prepare("SELECT id FROM events WHERE code = ?");
        $check->execute([$code]);
        if (!$check->fetch()) break;
        $code = $baseCode . '-' . $counter;
        $counter++;
    }

    $description = trim($data['description'] ?? '');
    $event_date = trim($data['event_date'] ?? '');
    $location = trim($data['location'] ?? '');
    $admin_pin = trim($data['admin_pin'] ?? '1234');
    $theme = trim($data['theme'] ?? 'celebration');

    $stmt = $pdo->prepare("
        INSERT INTO events (code, title, description, event_date, location, admin_pin, theme)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$code, $title, $description, $event_date, $location, $admin_pin, $theme]);
    $eventId = $pdo->lastInsertId();

    // URL destino del evento con PIN integrado para acceso instantáneo por QR
    $baseUrl = getBaseUrl();
    $targetUrl = $baseUrl . "/event.php?code=" . $code . "&pin=" . urlencode($admin_pin);

    // Generar y cachear imagen del Código QR
    $qrDir = __DIR__ . '/uploads/qrcodes';
    if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);
    $qrFilename = "qr_{$code}.png";
    $qrPath = $qrDir . '/' . $qrFilename;

    // Usar servicio seguro de QR y cachear localmente
    $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($targetUrl);
    $qrImage = @file_get_contents($qrApiUrl);
    if ($qrImage) {
        file_put_contents($qrPath, $qrImage);
        $qrUrl = "uploads/qrcodes/" . $qrFilename;
    } else {
        $qrUrl = $qrApiUrl; // Fallback
    }

    echo json_encode([
        'success' => true,
        'event' => [
            'id' => $eventId,
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'event_date' => $event_date,
            'location' => $location,
            'theme' => $theme,
            'media_count' => 0
        ],
        'qr_url' => $qrUrl,
        'target_url' => $targetUrl
    ]);
    exit;
}

// 3. OBTENER DETALLE DE EVENTO Y SUS MEDIOS (PROTEGIDO POR PIN)
if ($action === 'get_event') {
    $code = $_GET['code'] ?? '';
    $pin = trim($_GET['pin'] ?? $_POST['pin'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM events WHERE code = ?");
    $stmt->execute([$code]);
    $event = $stmt->fetch();

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Evento no encontrado']);
        exit;
    }

    $baseUrl = getBaseUrl();
    $targetUrl = $baseUrl . "/event.php?code=" . $code . "&pin=" . urlencode($event['admin_pin']);
    
    // QR local o fallback
    $qrPath = __DIR__ . "/uploads/qrcodes/qr_{$code}.png";
    if (file_exists($qrPath)) {
        $qrUrl = "uploads/qrcodes/qr_{$code}.png";
    } else {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($targetUrl);
    }

    // Comprobar si el PIN ingresado es correcto
    if ($pin !== $event['admin_pin']) {
        echo json_encode([
            'locked' => true,
            'event' => [
                'id' => $event['id'],
                'code' => $event['code'],
                'title' => $event['title'],
                'event_date' => $event['event_date'],
                'location' => $event['location'],
                'theme' => $event['theme']
            ],
            'media' => [],
            'qr_url' => $qrUrl,
            'target_url' => $targetUrl,
            'message' => 'Evento protegido. Ingresa el PIN para desbloquear el álbum.'
        ]);
        exit;
    }

    // Si el PIN coincide: entregar todos los recuerdos
    $stmtMedia = $pdo->prepare("SELECT * FROM media WHERE event_id = ? ORDER BY created_at DESC");
    $stmtMedia->execute([$event['id']]);
    $media = $stmtMedia->fetchAll();

    $event['media_count'] = count($media);

    echo json_encode([
        'locked' => false,
        'event' => [
            'id' => $event['id'],
            'code' => $event['code'],
            'title' => $event['title'],
            'description' => $event['description'],
            'event_date' => $event['event_date'],
            'location' => $event['location'],
            'theme' => $event['theme'],
            'media_count' => count($media)
        ],
        'media' => $media,
        'qr_url' => $qrUrl,
        'target_url' => $targetUrl
    ]);
    exit;
}

// 4. SUBIR FOTO O VIDEO (VALIDA PIN)
if ($action === 'upload') {
    $code = $_POST['code'] ?? '';
    $pin = trim($_POST['pin'] ?? $_GET['pin'] ?? '');
    $uploader = trim($_POST['uploader_name'] ?? '');
    if (empty($uploader)) $uploader = 'Invitado especial';
    $caption = trim($_POST['caption'] ?? '');

    $stmt = $pdo->prepare("SELECT id, admin_pin FROM events WHERE code = ?");
    $stmt->execute([$code]);
    $event = $stmt->fetch();

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Evento no encontrado']);
        exit;
    }

    if ($pin !== $event['admin_pin']) {
        http_response_code(403);
        echo json_encode(['error' => 'PIN de acceso incorrecto para subir fotos a este evento']);
        exit;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibió ningún archivo o hubo un error en la subida']);
        exit;
    }

    $origName = $_FILES['file']['name'];
    $tmpPath = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    $imgExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic'];
    $vidExts = ['mp4', 'mov', 'webm', 'avi', 'm4v'];

    $fileType = 'unknown';
    if (in_array($ext, $imgExts)) $fileType = 'image';
    if (in_array($ext, $vidExts)) $fileType = 'video';

    if ($fileType === 'unknown') {
        http_response_code(400);
        echo json_encode(['error' => 'Formato no permitido. Solo se aceptan fotos o videos.']);
        exit;
    }

    $eventMediaDir = __DIR__ . "/uploads/media/{$code}";
    $eventThumbsDir = __DIR__ . "/uploads/thumbs/{$code}";
    if (!file_exists($eventMediaDir)) mkdir($eventMediaDir, 0777, true);
    if (!file_exists($eventThumbsDir)) mkdir($eventThumbsDir, 0777, true);

    $uniqueId = substr(bin2hex(random_bytes(8)), 0, 12);
    $destFilename = "{$uniqueId}.{$ext}";
    $destPath = "{$eventMediaDir}/{$destFilename}";
    $thumbFilename = "{$uniqueId}_thumb.jpg";
    $thumbPath = "{$eventThumbsDir}/{$thumbFilename}";

    if (!move_uploaded_file($tmpPath, $destPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el archivo en el servidor']);
        exit;
    }

    // Generar miniatura con GD
    if ($fileType === 'image') {
        createImageThumbnail($destPath, $thumbPath, $ext);
    } else {
        createVideoThumbnailPlaceholder($thumbPath);
    }

    $fileUrl = "uploads/media/{$code}/{$destFilename}";
    $thumbUrl = "uploads/thumbs/{$code}/{$thumbFilename}";

    $insertStmt = $pdo->prepare("
        INSERT INTO media (event_id, uploader_name, file_type, file_url, thumb_url, original_filename, file_size, caption, likes_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $insertStmt->execute([$event['id'], $uploader, $fileType, $fileUrl, $thumbUrl, $origName, $fileSize, $caption]);
    $mediaId = $pdo->lastInsertId();

    $selectStmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
    $selectStmt->execute([$mediaId]);
    $newMedia = $selectStmt->fetch();

    echo json_encode([
        'success' => true,
        'media' => $newMedia
    ]);
    exit;
}

// 5. DAR LIKE
if ($action === 'like') {
    $mediaId = intval($_POST['media_id'] ?? $_GET['media_id'] ?? 0);
    $pdo->prepare("UPDATE media SET likes_count = likes_count + 1 WHERE id = ?")->execute([$mediaId]);
    
    $stmt = $pdo->prepare("SELECT likes_count FROM media WHERE id = ?");
    $stmt->execute([$mediaId]);
    $res = $stmt->fetch();
    echo json_encode(['success' => true, 'likes_count' => $res['likes_count'] ?? 0]);
    exit;
}

// 6. BORRAR FOTO (CON PIN)
if ($action === 'delete') {
    $mediaId = intval($_POST['media_id'] ?? $_GET['media_id'] ?? 0);
    $pin = trim($_POST['pin'] ?? $_GET['pin'] ?? '');

    $stmt = $pdo->prepare("
        SELECT m.*, e.admin_pin 
        FROM media m 
        JOIN events e ON m.event_id = e.id 
        WHERE m.id = ?
    ");
    $stmt->execute([$mediaId]);
    $item = $stmt->fetch();

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Recuerdo no encontrado']);
        exit;
    }

    if ($pin !== $item['admin_pin']) {
        http_response_code(403);
        echo json_encode(['error' => 'PIN de anfitrión incorrecto']);
        exit;
    }

    // Borrar archivos
    @unlink(__DIR__ . '/' . $item['file_url']);
    @unlink(__DIR__ . '/' . $item['thumb_url']);

    $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$mediaId]);
    echo json_encode(['success' => true]);
    exit;
}

// 7. DESCARGAR TODO EN ARCHIVO ZIP
if ($action === 'download_zip') {
    $code = $_GET['code'] ?? '';
    $pin = trim($_GET['pin'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM events WHERE code = ?");
    $stmt->execute([$code]);
    $event = $stmt->fetch();

    if (!$event) {
        die("Evento no encontrado");
    }

    if (!empty($pin) && $pin !== $event['admin_pin']) {
        die("PIN de anfitrión incorrecto");
    }

    $mediaDir = __DIR__ . "/uploads/media/{$code}";
    if (!file_exists($mediaDir)) {
        die("Aún no hay fotos subidas en este evento.");
    }

    $files = scandir($mediaDir);
    $files = array_diff($files, ['.', '..']);
    if (empty($files)) {
        die("Aún no hay fotos subidas en este evento.");
    }

    $zipName = "recuerdos_{$code}.zip";
    $zipPath = sys_get_temp_dir() . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($files as $f) {
            $fullPath = $mediaDir . '/' . $f;
            if (is_file($fullPath)) {
                $zip->addFile($fullPath, $f);
            }
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        @unlink($zipPath);
        exit;
    } else {
        die("Error al crear el archivo ZIP.");
    }
}

// Helpers para imágenes con GD
function createImageThumbnail($source, $destination, $ext) {
    list($width, $height) = @getimagesize($source);
    if (!$width || !$height) return copy($source, $destination);

    $maxDim = 600;
    $scale = min($maxDim / $width, $maxDim / $height, 1);
    $newW = (int)($width * $scale);
    $newH = (int)($height * $scale);

    $srcImg = null;
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $srcImg = @imagecreatefromjpeg($source);
        // Manejar orientación EXIF si está disponible
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($source);
            if (!empty($exif['Orientation']) && $srcImg) {
                switch ($exif['Orientation']) {
                    case 3: $srcImg = imagerotate($srcImg, 180, 0); break;
                    case 6: $srcImg = imagerotate($srcImg, -90, 0); break;
                    case 8: $srcImg = imagerotate($srcImg, 90, 0); break;
                }
                $width = imagesx($srcImg);
                $height = imagesy($srcImg);
                $scale = min($maxDim / $width, $maxDim / $height, 1);
                $newW = (int)($width * $scale);
                $newH = (int)($height * $scale);
            }
        }
    } elseif ($ext === 'png') {
        $srcImg = @imagecreatefrompng($source);
    } elseif ($ext === 'webp') {
        $srcImg = @imagecreatefromwebp($source);
    }

    if (!$srcImg) return copy($source, $destination);

    $thumb = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);
    imagejpeg($thumb, $destination, 85);
    imagedestroy($thumb);
    imagedestroy($srcImg);
}

function createVideoThumbnailPlaceholder($destination) {
    $img = imagecreatetruecolor(480, 360);
    $bg = imagecolorallocate($img, 24, 24, 27);
    $pink = imagecolorallocate($img, 244, 63, 94);
    $white = imagecolorallocate($img, 255, 255, 255);

    imagefilledrectangle($img, 0, 0, 480, 360, $bg);
    imagefilledellipse($img, 240, 180, 80, 80, $pink);

    $points = [
        230, 160,
        230, 200,
        260, 180
    ];
    imagefilledpolygon($img, $points, 3, $white);

    imagejpeg($img, $destination, 85);
    imagedestroy($img);
}

echo json_encode(['error' => 'Acción no válida']);
