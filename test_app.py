import asyncio
import io
from pathlib import Path
from PIL import Image
from fastapi.testclient import TestClient

from main import app
from database import init_db
import config

client = TestClient(app)

def test_full_flow():
    print("1. Probando inicialización y creación de evento...")
    
    # Crear un evento de prueba
    event_payload = {
        "title": "Boda de Sofía & Carlos",
        "description": "¡Bienvenidos a nuestra celebración! Tomen muchas fotos.",
        "event_date": "15 de Diciembre, 2026",
        "location": "Hacienda Los Rosales",
        "admin_pin": "5678",
        "theme": "wedding",
        "custom_code": "boda-sofia-carlos-test"
    }
    
    res = client.post("/api/events", json=event_payload)
    assert res.status_code == 200, f"Error al crear evento: {res.text}"
    data = res.json()
    assert data["success"] is True
    event_code = data["event"]["code"]
    print(f"   -> Evento creado con código: {event_code}")
    print(f"   -> QR URL: {data['qr_url']}")

    # 2. Verificar que la imagen del código QR existe en el disco
    qr_filename = f"qr_{event_code}.png"
    qr_file = config.QR_DIR / qr_filename
    assert qr_file.exists(), "El archivo de código QR no fue generado en disco."
    print("   -> Archivo QR PNG generado exitosamente en disco.")

    # 3. Consultar detalles del evento
    # Sin PIN: debe estar bloqueado
    res_locked = client.get(f"/api/events/{event_code}")
    assert res_locked.status_code == 200
    assert res_locked.json()["locked"] is True
    assert len(res_locked.json()["media"]) == 0
    print("   -> Evento protegido correctamente: sin PIN esta bloqueado.")

    # Con PIN correcto: debe estar desbloqueado
    res = client.get(f"/api/events/{event_code}?pin=5678")
    assert res.status_code == 200
    details = res.json()
    assert details["locked"] is False
    assert details["event"]["title"] == "Boda de Sofía & Carlos"
    assert len(details["media"]) == 0
    print("   -> Consulta con PIN correcto OK: evento desbloqueado.")

    # 4. Simular subida de una foto tomada por un invitado
    print("2. Probando subida de foto de un invitado...")
    img = Image.new("RGB", (800, 600), color=(244, 63, 94))
    img_byte_arr = io.BytesIO()
    img.save(img_byte_arr, format="JPEG")
    img_bytes = img_byte_arr.getvalue()

    # Intento de subida sin PIN -> Debe dar 403
    bad_upload = client.post(
        f"/api/events/{event_code}/upload",
        data={"pin": "wrong", "uploader_name": "Intruso"},
        files={"file": ("foto_novios.jpg", img_bytes, "image/jpeg")}
    )
    assert bad_upload.status_code == 403, "Subida sin PIN valido debe ser rechazada"
    print("   -> Subida no autorizada rechazada correctamente con 403.")

    # Subida con PIN valido
    upload_res = client.post(
        f"/api/events/{event_code}/upload",
        data={"pin": "5678", "uploader_name": "Tía Carmen", "caption": "¡Vivan los novios!"},
        files={"file": ("foto_novios.jpg", img_bytes, "image/jpeg")}
    )
    assert upload_res.status_code == 200, f"Error al subir foto: {upload_res.text}"
    media_data = upload_res.json()["media"]
    media_id = media_data["id"]
    assert media_data["uploader_name"] == "Tía Carmen"
    assert media_data["file_type"] == "image"
    print(f"   -> Foto subida exitosamente con ID: {media_id}")

    # 5. Probar reaccionar con 'Me gusta'
    print("3. Probando boton de like...")
    like_res = client.post(f"/api/media/{media_id}/like")
    assert like_res.status_code == 200
    assert like_res.json()["likes_count"] == 1
    print("   -> Like registrado correctamente.")

    # 6. Probar empaquetado y descarga masiva en archivo ZIP
    print("4. Probando generacion de archivo ZIP para el anfitrion...")
    zip_res = client.get(f"/api/events/{event_code}/download-zip?pin=5678")
    assert zip_res.status_code == 200
    assert zip_res.headers["content-type"] == "application/zip"
    assert len(zip_res.content) > 0
    print(f"   -> Archivo ZIP generado con tamano: {len(zip_res.content)} bytes.")

    # 7. Probar eliminacion de foto con PIN
    print("5. Probando moderacion/borrado de foto con PIN...")
    # Intento con PIN incorrecto
    bad_del = client.delete(f"/api/media/{media_id}?pin=9999")
    assert bad_del.status_code == 403, "Deberia rechazar PIN incorrecto"
    # Intento con PIN correcto
    good_del = client.delete(f"/api/media/{media_id}?pin=5678")
    assert good_del.status_code == 200
    print("   -> Foto eliminada exitosamente con PIN correcto.")

    print("\n[OK] TODAS LAS PRUEBAS PASARON SATISFACTORIAMENTE!")

if __name__ == "__main__":
    asyncio.run(init_db())
    test_full_flow()
