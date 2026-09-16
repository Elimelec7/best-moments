import os
import uuid
import shutil
import zipfile
from pathlib import Path
from typing import Tuple, Dict, Any, Optional
import aiofiles
from fastapi import UploadFile, HTTPException
from PIL import Image, ImageOps, ImageDraw, ImageFont
import qrcode
from qrcode.image.styledpil import StyledPilImage
from qrcode.image.styles.moduledrawers import RoundedModuleDrawer
from qrcode.image.styles.colormasks import RadialGradiantColorMask

import config

def get_event_media_dir(event_code: str) -> Path:
    d = config.MEDIA_DIR / event_code
    d.mkdir(parents=True, exist_ok=True)
    return d

def get_event_thumbs_dir(event_code: str) -> Path:
    d = config.THUMBS_DIR / event_code
    d.mkdir(parents=True, exist_ok=True)
    return d

def get_file_type(extension: str) -> str:
    ext = extension.lower()
    if ext in config.ALLOWED_IMAGE_EXTENSIONS:
        return "image"
    if ext in config.ALLOWED_VIDEO_EXTENSIONS:
        return "video"
    return "unknown"

def generate_video_thumbnail(output_path: Path, filename: str):
    """Crea una miniatura elegante para videos con icono de reproducción"""
    w, h = 480, 360
    img = Image.new("RGB", (w, h), color=(24, 24, 27)) # Fondo oscuro elegante
    draw = ImageDraw.Draw(img)

    # Dibujar círculo central para botón de play
    cx, cy = w // 2, h // 2
    r = 45
    draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=(244, 63, 94)) # Rosa festivo

    # Triángulo de Play ▶
    play_coords = [
        (cx - 12, cy - 20),
        (cx - 12, cy + 20),
        (cx + 22, cy)
    ]
    draw.polygon(play_coords, fill=(255, 255, 255))

    # Guardar miniatura
    img.save(output_path, "WEBP", quality=85)

async def save_uploaded_media(file: UploadFile, event_code: str) -> Dict[str, Any]:
    """Guarda un archivo multimedia (foto o video), valida y genera miniatura."""
    original_filename = file.filename or "archivo"
    ext = Path(original_filename).suffix.lower()

    if ext not in config.ALLOWED_EXTENSIONS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato no permitido ({ext}). Formatos aceptados: fotos (JPG, PNG, WebP, HEIC) y videos (MP4, MOV, WebM)."
        )

    file_type = get_file_type(ext)
    unique_id = f"{uuid.uuid4().hex[:12]}"
    filename = f"{unique_id}{ext}"
    
    media_dir = get_event_media_dir(event_code)
    thumbs_dir = get_event_thumbs_dir(event_code)
    
    file_path = media_dir / filename
    thumb_filename = f"{unique_id}_thumb.webp"
    thumb_path = thumbs_dir / thumb_filename

    # Guardar archivo original
    total_size = 0
    async with aiofiles.open(file_path, "wb") as out_file:
        while chunk := await file.read(1024 * 1024): # Chunks de 1MB
            total_size += len(chunk)
            if total_size > config.MAX_FILE_SIZE_BYTES:
                # Si excede el tamaño, eliminar lo guardado
                if file_path.exists():
                    file_path.unlink()
                raise HTTPException(
                    status_code=413,
                    detail=f"El archivo supera el tamaño máximo permitido de {config.MAX_FILE_SIZE_MB}MB."
                )
            await out_file.write(chunk)

    # Generar miniatura
    if file_type == "image":
        try:
            with Image.open(file_path) as img:
                # Corregir rotación automática EXIF tomada desde teléfonos iPhone/Android
                img = ImageOps.exif_transpose(img)
                if img.mode in ("RGBA", "P"):
                    img = img.convert("RGB")
                
                # Redimensionar para miniatura rápida en móviles
                img.thumbnail((600, 600), Image.Resampling.LANCZOS)
                img.save(thumb_path, "WEBP", quality=82, optimize=True)
        except Exception as e:
            # Fallback en caso de formato exótico
            if file_path.exists():
                shutil.copyfile(file_path, thumb_path)
    else:
        # Generar thumbnail para video
        generate_video_thumbnail(thumb_path, original_filename)

    file_url = f"/uploads/media/{event_code}/{filename}"
    thumb_url = f"/uploads/thumbs/{event_code}/{thumb_filename}"

    return {
        "file_url": file_url,
        "thumb_url": thumb_url,
        "file_type": file_type,
        "file_size": total_size,
        "original_filename": original_filename
    }

def generate_event_qr(event_code: str, target_url: str) -> str:
    """Genera un código QR de alta resolución con diseño moderno para el evento."""
    qr = qrcode.QRCode(
        version=None,
        error_correction=qrcode.constants.ERROR_CORRECT_Q, # Alto nivel de corrección para fácil lectura
        box_size=12,
        border=3,
    )
    qr.add_data(target_url)
    qr.make(fit=True)

    # Crear imagen en alta definición
    qr_img = qr.make_image(fill_color="#18181b", back_color="#ffffff").convert("RGB")
    
    qr_filename = f"qr_{event_code}.png"
    qr_path = config.QR_DIR / qr_filename
    qr_img.save(qr_path, "PNG")

    return f"/uploads/qrcodes/{qr_filename}"

def create_event_zip(event_code: str, event_title: str) -> Optional[Path]:
    """Empaqueta todas las fotos y videos del evento en un único archivo ZIP para descarga del anfitrión."""
    media_dir = config.MEDIA_DIR / event_code
    if not media_dir.exists() or not any(media_dir.iterdir()):
        return None

    zip_filename = f"{event_code}_recuerdos.zip"
    zip_path = config.UPLOAD_DIR / zip_filename

    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zipf:
        for file_p in media_dir.iterdir():
            if file_p.is_file():
                zipf.write(file_p, arcname=file_p.name)

    return zip_path

def delete_media_files(file_url: str, thumb_url: str):
    """Elimina los archivos del disco cuando se borra un recuerdo."""
    try:
        # Extraer rutas relativas
        if file_url.startswith("/uploads/"):
            rel_path = file_url.replace("/uploads/", "")
            full_path = config.UPLOAD_DIR / rel_path
            if full_path.exists():
                full_path.unlink()

        if thumb_url.startswith("/uploads/"):
            rel_thumb = thumb_url.replace("/uploads/", "")
            full_thumb = config.UPLOAD_DIR / rel_thumb
            if full_thumb.exists():
                full_thumb.unlink()
    except Exception as e:
        print(f"Error al eliminar archivos locales: {e}")
