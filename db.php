<?php
// Configuración y conexión a la Base de Datos SQLite
$dbPath = __DIR__ . '/data/moments.db';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Crear tablas si no existen
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            event_date TEXT,
            location TEXT,
            admin_pin TEXT DEFAULT '1234',
            theme TEXT DEFAULT 'celebration',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_id INTEGER NOT NULL,
            uploader_name TEXT DEFAULT 'Invitado especial',
            file_type TEXT NOT NULL,
            file_url TEXT NOT NULL,
            thumb_url TEXT NOT NULL,
            original_filename TEXT,
            file_size INTEGER DEFAULT 0,
            caption TEXT,
            likes_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        );
        CREATE INDEX IF NOT EXISTS idx_media_event ON media(event_id);
    ");
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
