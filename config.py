import os
from pathlib import Path
from dotenv import load_dotenv

# Cargar variables de entorno si existe .env
load_dotenv()

BASE_DIR = Path(__file__).resolve().parent

# Configuración básica
APP_NAME = os.getenv("APP_NAME", "Moments Drive - Álbum de Fotos y Videos")
SECRET_KEY = os.getenv("SECRET_KEY", "moments-secret-key-change-in-production")
DEBUG = os.getenv("DEBUG", "true").lower() in ("true", "1", "yes")

# URL Base pública (usada para los códigos QR). Si está vacía, se usará la URL del Host de la petición
PUBLIC_URL = os.getenv("PUBLIC_URL", "").rstrip("/")

# Directorios de almacenamiento local
UPLOAD_DIR = BASE_DIR / "uploads"
MEDIA_DIR = UPLOAD_DIR / "media"
THUMBS_DIR = UPLOAD_DIR / "thumbs"
QR_DIR = UPLOAD_DIR / "qrcodes"

# Asegurar directorios
for directory in [UPLOAD_DIR, MEDIA_DIR, THUMBS_DIR, QR_DIR]:
    directory.mkdir(parents=True, exist_ok=True)

# Base de datos SQLite
DATABASE_URL = os.getenv("DATABASE_URL", f"sqlite+aiosqlite:///{BASE_DIR / 'moments.db'}")

# Límites de archivos
MAX_FILE_SIZE_MB = int(os.getenv("MAX_FILE_SIZE_MB", "100"))
MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024

ALLOWED_IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".gif", ".heic"}
ALLOWED_VIDEO_EXTENSIONS = {".mp4", ".mov", ".avi", ".webm", ".m4v"}
ALLOWED_EXTENSIONS = ALLOWED_IMAGE_EXTENSIONS | ALLOWED_VIDEO_EXTENSIONS

# Integración Nube Opcional (Cloudinary)
CLOUDINARY_CLOUD_NAME = os.getenv("CLOUDINARY_CLOUD_NAME", "")
CLOUDINARY_API_KEY = os.getenv("CLOUDINARY_API_KEY", "")
CLOUDINARY_API_SECRET = os.getenv("CLOUDINARY_API_SECRET", "")
USE_CLOUDINARY = bool(CLOUDINARY_CLOUD_NAME and CLOUDINARY_API_KEY and CLOUDINARY_API_SECRET)
