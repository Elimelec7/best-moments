from datetime import datetime
from typing import AsyncGenerator
from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import declarative_base, relationship
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession, async_sessionmaker

import config

Base = declarative_base()

class Event(Base):
    __tablename__ = "events"

    id = Column(Integer, primary_key=True, index=True)
    code = Column(String(64), unique=True, index=True, nullable=False) # Código corto para URL y QR
    title = Column(String(200), nullable=False)
    description = Column(Text, nullable=True)
    event_date = Column(String(50), nullable=True)
    location = Column(String(200), nullable=True)
    admin_pin = Column(String(20), default="1234") # PIN para administrar fotos / borrar / descargar
    cover_url = Column(String(500), nullable=True)
    theme = Column(String(50), default="celebration") # celebration, wedding, birthday, elegant
    created_at = Column(DateTime, default=datetime.utcnow)

    # Relación con las fotos y videos
    media_items = relationship("Media", back_populates="event", cascade="all, delete-orphan", order_by="desc(Media.created_at)")

    def to_dict(self, media_count: int = 0):
        return {
            "id": self.id,
            "code": self.code,
            "title": self.title,
            "description": self.description,
            "event_date": self.event_date,
            "location": self.location,
            "cover_url": self.cover_url,
            "theme": self.theme,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "media_count": media_count
        }


class Media(Base):
    __tablename__ = "media"

    id = Column(Integer, primary_key=True, index=True)
    event_id = Column(Integer, ForeignKey("events.id", ondelete="CASCADE"), nullable=False, index=True)
    uploader_name = Column(String(100), default="Invitado especial")
    file_type = Column(String(20), nullable=False) # "image" o "video"
    file_url = Column(String(500), nullable=False)
    thumb_url = Column(String(500), nullable=False)
    original_filename = Column(String(255), nullable=True)
    file_size = Column(Integer, default=0) # en bytes
    caption = Column(String(500), nullable=True)
    likes_count = Column(Integer, default=0)
    created_at = Column(DateTime, default=datetime.utcnow, index=True)

    event = relationship("Event", back_populates="media_items")

    def to_dict(self):
        return {
            "id": self.id,
            "event_id": self.event_id,
            "uploader_name": self.uploader_name or "Invitado especial",
            "file_type": self.file_type,
            "file_url": self.file_url,
            "thumb_url": self.thumb_url,
            "original_filename": self.original_filename,
            "file_size": self.file_size,
            "caption": self.caption or "",
            "likes_count": self.likes_count,
            "created_at": self.created_at.isoformat() if self.created_at else None
        }


# Motor de Base de Datos Asíncrono
engine = create_async_engine(
    config.DATABASE_URL,
    echo=False,
    connect_args={"check_same_thread": False} if "sqlite" in config.DATABASE_URL else {}
)

AsyncSessionLocal = async_sessionmaker(
    bind=engine,
    class_=AsyncSession,
    expire_on_commit=False
)

async def init_db():
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

async def get_db() -> AsyncGenerator[AsyncSession, None]:
    async with AsyncSessionLocal() as session:
        try:
            yield session
        finally:
            await session.close()
