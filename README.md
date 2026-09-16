# ✨ Experience your best moments with us! - Álbum en Vivo para Eventos con Código QR

Plataforma interactiva, moderna y dinámica para eventos especiales (**Bodas, Cumpleaños, Graduaciones, Fiestas, Bautizos, etc.**). Permite a los anfitriones generar un **código QR** para colocar en las mesas o invitaciones. Los invitados simplemente escanean el QR con la cámara de su teléfono y, **sin registrarse ni instalar ninguna aplicación**, pueden capturar y compartir recuerdos en tiempo real.
1. Tomar fotos y videos directamente desde la cámara del celular.
2. Subir fotos existentes de su galería.
3. Ingresar su nombre para que todos sepan quién capturó el momento.
4. Ver la galería compartida en tiempo real y reaccionar con ❤️.
5. Proyectar las fotos en vivo en pantallas o televisores durante la fiesta ("Modo Proyector").
6. Descargar todas las fotos en alta resolución en un único archivo ZIP para los anfitriones.

---

## 🚀 Características Principales

- **📱 Experiencia Mobile-First sin Instalaciones:** Funciona directamente en Safari (iPhone) y Chrome (Android).
- **🔲 Generador Automático de Código QR:** Genera un QR de alta resolución con enlace directo a la celebración.
- **🖨️ Cartel Imprimible para Mesas:** Plantilla prediseñada lista para imprimir con el QR y 3 sencillos pasos.
- **🖼️ Miniaturas Automáticas y Corrección de Giro:** Las fotos se optimizan al instante y se corrige la orientación EXIF de los celulares.
- **📺 Modo Pantalla / Proyector en Vivo:** Conecta una laptop al proyector o Smart TV de la fiesta; las fotos de los invitados aparecen en pantalla completa automáticamente cada pocos segundos con código QR en la esquina.
- **📦 Descarga Masiva en ZIP:** El anfitrión puede descargar todos los recuerdos originales en un clic con su PIN de seguridad.
- **🛡️ Moderación con PIN:** Permite borrar cualquier foto inapropiada ingresando el PIN configurado por el anfitrión.
- **☁️ Almacenamiento Dual (Local o Cloudinary):** Funciona al 100% de forma local o conectado a Cloudinary en la nube.

---

## 🛠️ Requisitos Previos

- **Python 3.10 o superior** (Detectado Python 3.11 en este sistema).

---

## ⚡ Inicio Rápido (En tu Computadora)

El entorno virtual (`venv`) ya se encuentra creado y configurado con todas sus dependencias.

### 1. Iniciar el servidor
Ejecuta en PowerShell dentro de esta carpeta:
```powershell
.\venv\Scripts\python -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

### 2. Abrir en tu navegador
- Visita: **[http://localhost:8000](http://localhost:8000)**
- Crea tu primer evento (ej: *"Boda de Sofía & Carlos"* o *"Mis 15 Años"*).
- Al guardar, verás el **código QR generado**, el enlace directo y los accesos al cartel de mesas y modo proyector.

---

## 📲 ¿Cómo probarlo desde tu celular en la misma red Wi-Fi?

Al arrancar con `--host 0.0.0.0`, el servidor está disponible para cualquier dispositivo conectado al mismo Wi-Fi de tu casa o local:

1. Abre una terminal y averigua la dirección IP de tu computadora con:
   ```powershell
   ipconfig
   ```
   (Busca la línea `Dirección IPv4`, por ejemplo: `192.168.1.50`).
2. En el archivo `.env` o en la configuración, puedes definir:
   ```env
   PUBLIC_URL=http://192.168.1.50:8000
   ```
3. Desde tu celular conectado al mismo Wi-Fi, abre el navegador y entra a:
   `http://192.168.1.50:8000` o escanea el código QR generado.
4. ¡Toma una foto con tu cámara y mira cómo aparece al instante en la pantalla de tu computadora!

---

## 🌐 Publicarlo en la Nube (Totalmente Gratis y con HTTPS)

Para que los invitados puedan escanear el QR desde cualquier lugar usando sus datos móviles (4G/5G) o cualquier red, se recomienda desplegarlo en la nube:

### Opción A: Despliegue en Render.com (Recomendado, Gratis)
1. Sube este repositorio a **GitHub**.
2. Entra a [render.com](https://render.com) y crea una cuenta gratuita.
3. Haz clic en **"New" -> "Web Service"** y conecta tu repositorio de GitHub.
4. Render detectará automáticamente el archivo `Dockerfile` o puedes seleccionar:
   - **Environment:** Python
   - **Build Command:** `pip install -r requirements.txt`
   - **Start Command:** `uvicorn main:app --host 0.0.0.0 --port $PORT`
5. En las variables de entorno de Render, añade:
   - `PUBLIC_URL` = `https://tu-app.onrender.com`
6. ¡Listo! Render te proporcionará un enlace seguro con `https://` y los códigos QR funcionarán automáticamente con cualquier teléfono.

### Opción B: Probar en Internet al Instante con un Túnel (ngrok o localtunnel)
Si estás en la fiesta y quieres que funcione al instante con tu laptop:
```powershell
# Usando npx localtunnel (no requiere registro)
npx localtunnel --port 8000
```
Copia la URL pública generada (ej: `https://fiesta-fotos.loca.lt`) y colócala en `PUBLIC_URL` en tu archivo `.env`.

---

## ☁️ Configurar Almacenamiento en la Nube con Cloudinary (Opcional)

Si deseas que las fotos se almacenen en Cloudinary (capa gratuita de hasta 25 GB de fotos y videos):
1. Regístrate gratis en [cloudinary.com](https://cloudinary.com).
2. En tu Dashboard, copia:
   - Cloud Name
   - API Key
   - API Secret
3. Crea un archivo `.env` (o edita las variables de entorno) con:
   ```env
   CLOUDINARY_CLOUD_NAME=tu_cloud_name
   CLOUDINARY_API_KEY=tu_api_key
   CLOUDINARY_API_SECRET=tu_api_secret
   ```

---

## 📂 Estructura del Proyecto

```
resilient-carson/
├── config.py             # Configuración, directorios y variables de entorno
├── database.py           # Modelos SQLite y SQLAlchemy asíncrono
├── storage.py            # Almacenamiento, miniaturas PIL, QR codes y empaque ZIP
├── main.py               # Servidor FastAPI y API REST
├── test_app.py           # Pruebas automatizadas de todo el flujo
├── requirements.txt      # Dependencias de Python
├── Dockerfile            # Configuración para despliegue en la nube
├── .env.example          # Plantilla de variables de entorno
├── static/
│   ├── index.html        # Portada y creador de eventos
│   ├── event.html        # Vista del invitado (cámara, galería, likes)
│   ├── slideshow.html    # Modo proyector / TV en vivo
│   ├── qr-poster.html    # Cartel imprimible para mesas
│   ├── css/styles.css    # Estilos CSS táctiles y responsivos
│   └── js/app.js         # Lógica cliente, subida asíncrona y barra de progreso
└── uploads/              # Carpeta de almacenamiento local (fotos, videos, QR)
```

---

## 📋 Verificación de Calidad

Para ejecutar la batería de pruebas automatizadas:
```powershell
.\venv\Scripts\python.exe test_app.py
```
Resultado:
- Creación de evento y validación de código único ✅
- Generación de código QR PNG de alta definición ✅
- Subida de archivos multimedia con metadatos de invitado ✅
- Contador de likes en tiempo real ❤️ ✅
- Empaquetado de álbum completo en archivo ZIP para el anfitrión ✅
- Moderación y borrado seguro con PIN ✅
