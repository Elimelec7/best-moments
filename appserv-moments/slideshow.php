<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modo Proyector en Vivo - Moments Drive</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #000;
      color: #fff;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      overflow: hidden;
      height: 100vh;
      width: 100vw;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #slideshowContainer {
      position: relative;
      width: 100vw;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .slide-media {
      max-width: 95vw;
      max-height: 90vh;
      object-fit: contain;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.8);
      transition: opacity 0.8s ease-in-out;
      opacity: 0;
    }
    .slide-media.active { opacity: 1; }
    .qr-badge-corner {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: rgba(15, 23, 42, 0.88);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 14px;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
      z-index: 100;
      animation: pulse 3s infinite;
    }
    .qr-badge-corner img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      background: #fff;
      padding: 3px;
    }
    .qr-text { text-align: left; }
    .qr-text h4 { font-size: 0.95rem; font-weight: 700; color: #f43f5e; margin-bottom: 3px; }
    .qr-text p { font-size: 0.8rem; color: #cbd5e1; }
    .slide-caption-bar {
      position: fixed;
      bottom: 24px;
      left: 24px;
      background: rgba(0, 0, 0, 0.7);
      border-radius: 24px;
      padding: 8px 20px;
      font-size: 1rem;
      color: #e2e8f0;
      backdrop-filter: blur(8px);
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 100;
    }
    .event-top-bar {
      position: fixed;
      top: 20px;
      left: 24px;
      font-size: 1.2rem;
      font-weight: 700;
      color: #fff;
      text-shadow: 0 2px 10px rgba(0,0,0,0.8);
      z-index: 100;
    }
    .controls-top-right {
      position: fixed;
      top: 20px;
      right: 24px;
      display: flex;
      gap: 10px;
      z-index: 100;
    }
    .btn-ctrl {
      background: rgba(255,255,255,0.15);
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
      backdrop-filter: blur(6px);
      font-size: 0.85rem;
    }
    .btn-ctrl:hover { background: rgba(255,255,255,0.25); }
    .empty-waiting { text-align: center; color: #94a3b8; }
    .empty-waiting h2 { color: #fff; margin-bottom: 12px; font-size: 2rem; }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.02); }
    }
  </style>
</head>
<body>
  <div class="event-top-bar" id="eventTitle">Moments Drive - Modo Pantalla</div>
  
  <div class="controls-top-right">
    <button class="btn-ctrl" onclick="toggleFullscreen()">⛶ Pantalla Completa</button>
    <button class="btn-ctrl" id="btnPause" onclick="togglePlayPause()">⏸ Pausar</button>
  </div>

  <div id="slideshowContainer">
    <div class="empty-waiting" id="waitingState">
      <h2>🎉 ¡Esperando fotos de los invitados!</h2>
      <p style="font-size: 1.1rem; margin-bottom: 24px;">Apunta tu cámara al código QR y sé el primero en salir en la pantalla.</p>
    </div>
  </div>

  <div class="slide-caption-bar" id="captionBar" style="display: none;">
    <span>📸</span>
    <span id="uploaderText">Recuerdo</span>
  </div>

  <div class="qr-badge-corner" id="qrCornerBadge">
    <img id="qrCornerImg" src="" alt="QR">
    <div class="qr-text">
      <h4>¡Sube tus fotos aquí!</h4>
      <p>Apunta tu celular al QR</p>
    </div>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const eventCode = urlParams.get('code') || '';
    let mediaList = [];
    let currentIndex = -1;
    let timer = null;
    let isPaused = false;
    const SLIDE_INTERVAL = 6000;

    async function loadData() {
      if (!eventCode) return;
      try {
        const res = await fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}`);
        if (!res.ok) return;
        const data = await res.json();
        
        document.getElementById("eventTitle").textContent = data.event.title;
        document.getElementById("qrCornerImg").src = data.qr_url;

        const newMedia = data.media || [];
        if (newMedia.length !== mediaList.length) {
          mediaList = newMedia;
          if (currentIndex === -1 && mediaList.length > 0) {
            nextSlide();
          }
        }
      } catch (e) {
        console.error(e);
      }
    }

    function showSlide(index) {
      if (!mediaList || mediaList.length === 0) return;
      if (index >= mediaList.length) index = 0;
      if (index < 0) index = mediaList.length - 1;
      currentIndex = index;

      const media = mediaList[currentIndex];
      const container = document.getElementById("slideshowContainer");
      const waiting = document.getElementById("waitingState");
      const captionBar = document.getElementById("captionBar");
      const uploaderText = document.getElementById("uploaderText");

      if (waiting) waiting.style.display = "none";
      if (captionBar) captionBar.style.display = "flex";
      if (uploaderText) uploaderText.textContent = `Compartido por: ${media.uploader_name} ❤️ ${media.likes_count}`;

      let mediaHtml = "";
      if (media.file_type === "video") {
        mediaHtml = `<video class="slide-media active" autoplay muted playsinline loop>
          <source src="${media.file_url}" type="video/mp4">
        </video>`;
      } else {
        mediaHtml = `<img src="${media.file_url}" class="slide-media active" alt="Foto">`;
      }

      container.innerHTML = mediaHtml;
    }

    function nextSlide() {
      if (isPaused || mediaList.length === 0) return;
      showSlide(currentIndex + 1);
    }

    function togglePlayPause() {
      isPaused = !isPaused;
      document.getElementById("btnPause").textContent = isPaused ? "▶ Reanudar" : "⏸ Pausar";
    }

    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        document.exitFullscreen().catch(() => {});
      }
    }

    loadData().then(() => {
      timer = setInterval(nextSlide, SLIDE_INTERVAL);
      setInterval(loadData, 8000);
    });
  </script>
</body>
</html>
