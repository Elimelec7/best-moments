<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Experience your best moments with us! - Modo Pantalla en Vivo</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #090d16;
      color: #fff;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      overflow: hidden;
      height: 100vh;
      width: 100vw;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    /* Fondo ambiental dinámico que replica la foto actual */
    #ambientBackdrop {
      position: absolute;
      inset: -50px;
      background-size: cover;
      background-position: center;
      filter: blur(50px) brightness(0.4);
      opacity: 0.7;
      transition: background-image 1s ease-in-out;
      z-index: 1;
    }
    #slideshowContainer {
      position: relative;
      width: 100vw;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2;
    }
    .slide-media {
      max-width: 92vw;
      max-height: 88vh;
      object-fit: contain;
      border-radius: 20px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.85), 0 0 30px rgba(255, 51, 102, 0.25);
      transition: opacity 0.8s ease-in-out, transform 6s ease-out;
      opacity: 0;
      transform: scale(0.98);
    }
    .slide-media.active {
      opacity: 1;
      transform: scale(1.02);
    }
    /* Tarjeta Flotante Neón en la Esquina con Código QR */
    .qr-badge-corner {
      position: fixed;
      bottom: 26px;
      right: 26px;
      background: rgba(11, 15, 25, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      backdrop-filter: blur(16px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 20px rgba(255, 51, 102, 0.3);
      z-index: 100;
      animation: pulseGlow 3s infinite;
    }
    .qr-badge-corner img {
      width: 86px;
      height: 86px;
      border-radius: 12px;
      background: #fff;
      padding: 4px;
    }
    .qr-text h4 {
      font-size: 1.05rem;
      font-weight: 800;
      background: linear-gradient(135deg, #ff3366, #ff80bf);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 4px;
    }
    .qr-text p {
      font-size: 0.82rem;
      color: #e2e8f0;
      font-weight: 600;
    }
    /* Pie de Foto del Fotógrafo */
    .slide-caption-bar {
      position: fixed;
      bottom: 26px;
      left: 26px;
      background: rgba(11, 15, 25, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 30px;
      padding: 10px 24px;
      font-size: 1.05rem;
      font-weight: 700;
      color: #fff;
      backdrop-filter: blur(12px);
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
      z-index: 100;
    }
    /* Encabezado Superior de la Celebración */
    .event-top-bar {
      position: fixed;
      top: 22px;
      left: 26px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(11, 15, 25, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.12);
      padding: 8px 18px;
      border-radius: 30px;
      backdrop-filter: blur(10px);
      z-index: 100;
    }
    .event-top-title {
      font-size: 1.1rem;
      font-weight: 800;
      color: #fff;
    }
    .controls-top-right {
      position: fixed;
      top: 22px;
      right: 26px;
      display: flex;
      gap: 10px;
      z-index: 100;
    }
    .btn-ctrl {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      padding: 8px 16px;
      border-radius: 12px;
      cursor: pointer;
      backdrop-filter: blur(8px);
      font-size: 0.88rem;
      font-weight: 700;
      transition: background 0.2s;
    }
    .btn-ctrl:hover { background: rgba(255, 255, 255, 0.25); }
    .empty-waiting { text-align: center; color: #94a3b8; z-index: 2; padding: 20px; }
    .empty-waiting h2 {
      font-size: 2.4rem;
      font-weight: 900;
      margin-bottom: 14px;
      background: linear-gradient(135deg, #fff, #ffd1dc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    @keyframes pulseGlow {
      0%, 100% { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 15px rgba(255, 51, 102, 0.3); }
      50% { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 30px rgba(139, 92, 246, 0.55); }
    }
  </style>
</head>
<body>
  <div id="ambientBackdrop"></div>

  <div class="event-top-bar">
    <span style="font-size: 1.3rem;">✨</span>
    <span class="event-top-title" id="eventTitle">Experience your best moments with us!</span>
  </div>
  
  <div class="controls-top-right">
    <button class="btn-ctrl" onclick="toggleFullscreen()">⛶ Pantalla Completa</button>
    <button class="btn-ctrl" id="btnPause" onclick="togglePlayPause()">⏸ Pausar</button>
  </div>

  <div id="slideshowContainer">
    <div class="empty-waiting" id="waitingState">
      <div style="font-size: 3.8rem; margin-bottom: 12px;">🎉 📸</div>
      <h2>¡Esperando los mejores momentos!</h2>
      <p style="font-size: 1.2rem; color: #cbd5e1; max-width: 580px; margin: 0 auto;">
        Apunta tu celular al código QR en pantalla y sé el primero en salir en la fiesta.
      </p>
    </div>
  </div>

  <!-- Pie con autor del recuerdo -->
  <div class="slide-caption-bar" id="captionBar" style="display: none;">
    <span>📸</span>
    <span id="uploaderText">Recuerdo de la fiesta</span>
  </div>

  <!-- Código QR en la Esquina -->
  <div class="qr-badge-corner" id="qrCornerBadge">
    <img id="qrCornerImg" src="" alt="QR">
    <div class="qr-text">
      <h4>¡Sube tu foto ahora!</h4>
      <p>Apunta tu cámara aquí 📲</p>
    </div>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const eventCode = urlParams.get('code') || '';
    let mediaList = [];
    let currentIndex = -1;
    let timer = null;
    let isPaused = false;
    const SLIDE_INTERVAL = 6000; // 6 segundos

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
      const backdrop = document.getElementById("ambientBackdrop");
      const waiting = document.getElementById("waitingState");
      const captionBar = document.getElementById("captionBar");
      const uploaderText = document.getElementById("uploaderText");

      if (waiting) waiting.style.display = "none";
      if (captionBar) captionBar.style.display = "flex";
      if (uploaderText) uploaderText.innerHTML = `Compartido por: <strong>${escapeHtml(media.uploader_name)}</strong> &nbsp;•&nbsp; ❤️ ${media.likes_count}`;

      if (media.file_type === "image") {
        backdrop.style.backgroundImage = `url('${media.file_url}')`;
      }

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

    function escapeHtml(text) {
      if (!text) return "";
      const div = document.createElement("div");
      div.innerText = text;
      return div.innerHTML;
    }

    loadData().then(() => {
      timer = setInterval(nextSlide, SLIDE_INTERVAL);
      setInterval(loadData, 8000);
    });
  </script>
</body>
</html>
