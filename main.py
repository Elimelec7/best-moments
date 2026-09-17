import os
import re
from datetime import datetime
from typing import Optional, List
from pathlib import Path

from fastapi import FastAPI, Depends, HTTPException, UploadFile, File, Form, Header, Request, status
from fastapi.responses import FileResponse, RedirectResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, update
from pydantic import BaseModel

import config
from database import init_db, get_db, Event, Media
import storage

app = FastAPI(
    title=config.APP_NAME,
    description="Plataforma de álbum de fotos y videos para eventos con acceso por código QR.",
    version="1.0.0"
)

# Permitir CORS para accesos móviles o integraciones
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Montar directorios estáticos y de subida
app.mount("/static", StaticFiles(directory=str(config.BASE_DIR / "static")), name="static")
app.mount("/uploads", StaticFiles(directory=str(config.UPLOAD_DIR)), name="uploads")


@app.on_event("startup")
async def on_startup():
    await init_db()


# ==========================================
# RUTAS DE PÁGINAS WEB (HTML)
# ==========================================

@app.get("/")
async def home_page():
    return FileResponse(config.BASE_DIR / "static" / "index.html")

@app.get("/e/{code}")
async def event_page(code: str):
    return FileResponse(config.BASE_DIR / "static" / "event.html")

@app.get("/e/{code}/slideshow")
async def slideshow_page(code: str):
    return FileResponse(config.BASE_DIR / "static" / "slideshow.html")

@app.get("/e/{code}/poster")
async def poster_page(code: str):
    return FileResponse(config.BASE_DIR / "static" / "qr-poster.html")


# ==========================================
# ESQUEMAS PYDANTIC (API)
# ==========================================

class EventCreateRequest(BaseModel):
    title: str
    description: Optional[str] = None
    event_date: Optional[str] = None
    location: Optional[str] = None
    admin_pin: Optional[str] = "1234"
    theme: Optional[str] = "celebration"
    custom_code: Optional[str] = None

def slugify(text: str) -> str:
    text = text.lower().strip()
    text = re.sub(r"[^\w\s-]", "", text)
    text = re.sub(r"[\s_-]+", "-", text)
    return text.strip("-")


# ==========================================
# ENDPOINTS DE EVENTOS
# ==========================================

@app.post("/api/events")
async def create_event(
    req: EventCreateRequest,
    request: Request,
    db: AsyncSession = Depends(get_db)
):
    """Crea un nuevo evento y genera su código QR con la URL de acceso."""
    if not req.title.strip():
        raise HTTPException(status_code=400, detail="El título del evento es obligatorio.")

    # Generar código único para el evento
    base_slug = slugify(req.custom_code if req.custom_code else req.title)
    if not base_slug:
        base_slug = f"evento-{datetime.utcnow().strftime('%Y%m%d%H%M')}"

    # Asegurar que el slug sea único
    code = base_slug
    counter = 1
    while True:
        existing = await db.scalar(select(Event).where(Event.code == code))
        if not existing:
            break
        code = f"{base_slug}-{counter}"
        counter += 1

    admin_pin = (req.admin_pin or "1234").strip()

    event = Event(
        code=code,
        title=req.title.strip(),
        description=req.description.strip() if req.description else None,
        event_date=req.event_date.strip() if req.event_date else None,
        location=req.location.strip() if req.location else None,
        admin_pin=admin_pin,
        theme=req.theme or "celebration"
    )
    db.add(event)
    await db.commit()
    await db.refresh(event)

    # Determinar URL pública del evento para el código QR con PIN integrado
    if config.PUBLIC_URL:
        target_url = f"{config.PUBLIC_URL}/e/{code}?pin={admin_pin}"
    else:
        # Usar la URL base de la petición entrante
        base_url = str(request.base_url).rstrip("/")
        target_url = f"{base_url}/e/{code}?pin={admin_pin}"

    # Generar QR
    qr_url = storage.generate_event_qr(code, target_url)

    return {
        "success": True,
        "event": event.to_dict(media_count=0),
        "qr_url": qr_url,
        "target_url": target_url
    }

@app.get("/api/events")
async def list_recent_events(db: AsyncSession = Depends(get_db)):
    """Lista los eventos disponibles ordenados por fecha (sin exponer PIN)."""
    stmt = select(Event).order_by(Event.created_at.desc()).limit(20)
    result = await db.scalars(stmt)
    events = result.all()

    data = []
    for ev in events:
        count = await db.scalar(select(func.count(Media.id)).where(Media.event_id == ev.id))
        data.append(ev.to_dict(media_count=count or 0))

    return {"events": data}

class VerifyPinRequest(BaseModel):
    pin: str

@app.post("/api/events/{code}/verify-pin")
async def verify_event_pin(
    code: str,
    req: VerifyPinRequest,
    db: AsyncSession = Depends(get_db)
):
    """Verifica si un PIN coincide con el evento."""
    event = await db.scalar(select(Event).where(Event.code == code))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")
    return {"valid": (req.pin.strip() == event.admin_pin)}

@app.get("/api/events/{code}")
async def get_event_details(
    code: str,
    request: Request,
    pin: Optional[str] = None,
    db: AsyncSession = Depends(get_db)
):
    """Obtiene los detalles del evento y todas sus fotos y videos (requiere PIN válido)."""
    event = await db.scalar(select(Event).where(Event.code == code))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")

    # URL del código QR con PIN
    if config.PUBLIC_URL:
        target_url = f"{config.PUBLIC_URL}/e/{code}?pin={event.admin_pin}"
    else:
        base_url = str(request.base_url).rstrip("/")
        target_url = f"{base_url}/e/{code}?pin={event.admin_pin}"

    qr_url = storage.generate_event_qr(code, target_url)

    # Validar PIN
    clean_pin = (pin or "").strip()
    if clean_pin != event.admin_pin:
        return {
            "locked": True,
            "event": {
                "id": event.id,
                "code": event.code,
                "title": event.title,
                "event_date": event.event_date,
                "location": event.location,
                "theme": event.theme
            },
            "qr_url": qr_url,
            "target_url": target_url,
            "media": []
        }

    stmt = select(Media).where(Media.event_id == event.id).order_by(Media.created_at.desc())
    media_records = (await db.scalars(stmt)).all()

    return {
        "locked": False,
        "event": event.to_dict(media_count=len(media_records)),
        "qr_url": qr_url,
        "target_url": target_url,
        "media": [m.to_dict() for m in media_records]
    }

@app.get("/api/events/{code}/qr")
async def get_event_qr_image(code: str, request: Request, db: AsyncSession = Depends(get_db)):
    """Devuelve directamente la imagen del código QR para imprimir o proyectar."""
    event = await db.scalar(select(Event).where(Event.code == code))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")

    if config.PUBLIC_URL:
        target_url = f"{config.PUBLIC_URL}/e/{code}?pin={event.admin_pin}"
    else:
        base_url = str(request.base_url).rstrip("/")
        target_url = f"{base_url}/e/{code}?pin={event.admin_pin}"

    storage.generate_event_qr(code, target_url)
    qr_path = config.QR_DIR / f"qr_{code}.png"
    return FileResponse(qr_path, media_type="image/png")


# ==========================================
# ENDPOINTS DE SUBIDA Y GESTIÓN DE FOTOS/VIDEOS
# ==========================================

@app.post("/api/events/{code}/upload")
async def upload_media(
    code: str,
    file: UploadFile = File(...),
    pin: Optional[str] = Form(None),
    uploader_name: Optional[str] = Form(None),
    caption: Optional[str] = Form(None),
    db: AsyncSession = Depends(get_db)
):
    """Recibe y almacena fotos o videos subidos por los invitados (valida PIN)."""
    event = await db.scalar(select(Event).where(Event.code == code))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")

    clean_pin = (pin or "").strip()
    if clean_pin != event.admin_pin:
        raise HTTPException(status_code=403, detail="PIN de acceso incorrecto para subir fotos a este evento.")

    # Guardar y generar miniatura
    file_info = await storage.save_uploaded_media(file, code)

    clean_uploader = (uploader_name or "").strip()
    if not clean_uploader:
        clean_uploader = "Invitado especial"

    clean_caption = (caption or "").strip()

    media = Media(
        event_id=event.id,
        uploader_name=clean_uploader,
        file_type=file_info["file_type"],
        file_url=file_info["file_url"],
        thumb_url=file_info["thumb_url"],
        original_filename=file_info["original_filename"],
        file_size=file_info["file_size"],
        caption=clean_caption,
        likes_count=0
    )

    db.add(media)
    await db.commit()
    await db.refresh(media)

    return {
        "success": True,
        "media": media.to_dict(),
        "message": "¡Recuerdo compartido con éxito!"
    }

@app.post("/api/media/{media_id}/like")
async def like_media(media_id: int, db: AsyncSession = Depends(get_db)):
    """Añade un 'Me gusta' a una foto o video."""
    media = await db.scalar(select(Media).where(Media.id == media_id))
    if not media:
        raise HTTPException(status_code=404, detail="Recuerdo no encontrado.")

    media.likes_count = (media.likes_count or 0) + 1
    await db.commit()
    await db.refresh(media)

    return {"success": True, "likes_count": media.likes_count}

@app.delete("/api/media/{media_id}")
async def delete_media(
    media_id: int,
    pin: Optional[str] = None,
    db: AsyncSession = Depends(get_db)
):
    """Elimina una foto o video (requiere el PIN de anfitrión)."""
    media = await db.scalar(select(Media).where(Media.id == media_id))
    if not media:
        raise HTTPException(status_code=404, detail="Recuerdo no encontrado.")

    event = await db.scalar(select(Event).where(Event.id == media.event_id))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")

    if not pin or pin.strip() != event.admin_pin:
        raise HTTPException(status_code=403, detail="PIN de anfitrión incorrecto. No tienes permiso para borrar esta foto.")

    # Eliminar archivos del almacenamiento
    storage.delete_media_files(media.file_url, media.thumb_url)

    await db.delete(media)
    await db.commit()

    return {"success": True, "message": "Recuerdo eliminado correctamente."}

@app.get("/api/events/{code}/download-zip")
async def download_all_media_zip(
    code: str,
    pin: Optional[str] = None,
    db: AsyncSession = Depends(get_db)
):
    """Empaqueta todas las fotos y videos del evento en un ZIP para que el anfitrión las descargue."""
    event = await db.scalar(select(Event).where(Event.code == code))
    if not event:
        raise HTTPException(status_code=404, detail="Evento no encontrado.")

    # Si se configuró PIN, validarlo para la descarga completa
    if pin and pin.strip() != event.admin_pin:
        raise HTTPException(status_code=403, detail="PIN de anfitrión incorrecto.")

    zip_path = storage.create_event_zip(code, event.title)
    if not zip_path or not zip_path.exists():
        raise HTTPException(status_code=404, detail="Aún no hay fotos o videos subidos en este evento para descargar.")

    clean_title = slugify(event.title) or code
    filename = f"{clean_title}_fotos_recuerdos.zip"

    return FileResponse(
        path=zip_path,
        media_type="application/zip",
        filename=filename
    )
