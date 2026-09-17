<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Experience your best moments with us! - Modo Pantalla en Vivo</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --primary: #ff3366;
      --primary-glow: rgba(255, 51, 102, 0.4);
    }
    body {
      background: #070a12;
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

    /* Barra Superior de Progreso Cinemática (Estilo Reel de Video / Story) */
    .story-progress-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 5px;
      background: rgba(255, 255, 255, 0.12);
      z-index: 120;
    }
    .story-progress-bar {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #ff3366, #ff80bf, #8b5cf6, #38bdf8);
      box-shadow: 0 0 12px #ff3366;
      transition: width 0.1s linear;
    }

    /* Fondo ambiental dinámico con desenfoque de luz que replica la foto actual */
    #ambientBackdrop {
      position: absolute;
      inset: -50px;
      background-size: cover;
      background-position: center;
      filter: blur(60px) brightness(0.35);
      opacity: 0.8;
      transition: background-image 1.2s ease-in-out;
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
      max-width: 90vw;
      max-height: 84vh;
      object-fit: contain;
      border-radius: 24px;
      box-shadow: 0 25px 70px rgba(0,0,0,0.9), 0 0 35px rgba(255, 51, 102, 0.3);
      opacity: 0;
      transition: opacity 0.8s ease-in-out;
    }
    .slide-media.active {
      opacity: 1;
    }

    /* Efecto Ken Burns dinámico para fotos: animación de cámara flotante suave */
    .slide-photo.active {
      animation: kenBurnsCinematic 7s ease-in-out forwards;
    }
    @keyframes kenBurnsCinematic {
      0% { transform: scale(1.0) translate(0, 0); }
      50% { transform: scale(1.06) translate(-1%, -0.6%); }
      100% { transform: scale(1.02) translate(0.6%, 0.4%); }
    }

    .slide-video.active {
      transform: scale(1.0);
    }

    /* Encabezado Superior de la Celebración */
    .event-top-bar {
      position: fixed;
      top: 18px;
      left: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(11, 15, 25, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 8px 18px;
      border-radius: 30px;
      backdrop-filter: blur(14px);
      z-index: 100;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
    }
    .event-top-title {
      font-size: 1.08rem;
      font-weight: 800;
      color: #fff;
    }
    .slide-counter-pill {
      background: rgba(255, 51, 102, 0.2);
      border: 1px solid rgba(255, 51, 102, 0.4);
      color: #ffd1dc;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 800;
    }

    /* Reproductor y Ecualizador de Música Ambiental */
    .music-badge-player {
      position: fixed;
      top: 18px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(11, 15, 25, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.22);
      border-radius: 30px;
      padding: 8px 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      backdrop-filter: blur(14px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
      z-index: 100;
      cursor: pointer;
      transition: all 0.2s ease;
      user-select: none;
    }
    .music-badge-player:hover {
      background: rgba(18, 24, 40, 0.95);
      border-color: #ff3366;
    }
    .equalizer-bars {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 16px;
    }
    .eq-bar {
      width: 3px;
      background: #ff3366;
      border-radius: 2px;
      animation: eqBounce 1.2s infinite ease-in-out;
    }
    .eq-bar:nth-child(1) { height: 10px; animation-delay: 0.1s; }
    .eq-bar:nth-child(2) { height: 16px; animation-delay: 0.3s; }
    .eq-bar:nth-child(3) { height: 8px; animation-delay: 0.5s; }
    .eq-bar:nth-child(4) { height: 14px; animation-delay: 0.2s; }

    .equalizer-bars.paused .eq-bar {
      animation-play-state: paused;
      height: 4px;
    }
    @keyframes eqBounce {
      0%, 100% { height: 4px; }
      50% { height: 16px; }
    }

    .music-prompt-banner {
      position: fixed;
      top: 68px;
      left: 50%;
      transform: translateX(-50%);
      background: linear-gradient(135deg, rgba(255, 51, 102, 0.95), rgba(139, 92, 246, 0.95));
      color: #fff;
      padding: 8px 22px;
      border-radius: 24px;
      font-size: 0.88rem;
      font-weight: 800;
      cursor: pointer;
      z-index: 150;
      box-shadow: 0 10px 25px rgba(0,0,0,0.6);
      backdrop-filter: blur(8px);
      animation: pulseGlow 2.5s infinite;
      transition: all 0.25s ease;
    }
    .music-prompt-banner:hover {
      transform: translateX(-50%) scale(1.05);
    }

    /* Botones de Control */
    .controls-top-right {
      position: fixed;
      top: 18px;
      right: 24px;
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

    /* Pie de Foto del Fotógrafo / Invitado */
    .slide-caption-bar {
      position: fixed;
      bottom: 26px;
      left: 26px;
      background: rgba(11, 15, 25, 0.88);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 30px;
      padding: 10px 24px;
      font-size: 1.05rem;
      font-weight: 700;
      color: #fff;
      backdrop-filter: blur(14px);
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.6);
      z-index: 100;
      animation: fadeInUp 0.5s ease-out;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Tarjeta Flotante Neón en la Esquina con Código QR */
    .qr-badge-corner {
      position: fixed;
      bottom: 26px;
      right: 26px;
      background: rgba(11, 15, 25, 0.92);
      border: 1px solid rgba(255, 255, 255, 0.22);
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
      width: 84px;
      height: 84px;
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

    /* Notificación de nuevo recuerdo en vivo */
    .new-media-toast {
      position: fixed;
      top: 76px;
      right: 24px;
      background: linear-gradient(135deg, #f43f5e, #8b5cf6);
      color: #fff;
      padding: 12px 22px;
      border-radius: 30px;
      font-weight: 800;
      font-size: 0.95rem;
      box-shadow: 0 12px 30px rgba(0,0,0,0.6);
      z-index: 200;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: toastIn 0.4s ease-out;
    }
    @keyframes toastIn {
      from { opacity: 0; transform: translateX(80px); }
      to { opacity: 1; transform: translateX(0); }
    }

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
  <!-- Barra Superior de Progreso Cinemática -->
  <div class="story-progress-wrapper">
    <div class="story-progress-bar" id="storyProgressBar"></div>
  </div>

  <div id="ambientBackdrop"></div>

  <!-- Barra Superior Izquierda: Título y Contador -->
  <div class="event-top-bar">
    <span id="themeEmoji" style="font-size: 1.3rem;">✨</span>
    <span class="event-top-title" id="eventTitle">Experience your best moments with us!</span>
    <span class="slide-counter-pill" id="slideCounterPill" style="display: none;">0 / 0</span>
  </div>

  <!-- Reproductor Flotante de Música Ambiental acorde a la Temática -->
  <div class="music-badge-player" id="musicPlayerBadge" onclick="toggleMusic()" title="Clic para reproducir/pausar música">
    <div class="equalizer-bars paused" id="eqBars">
      <div class="eq-bar"></div>
      <div class="eq-bar"></div>
      <div class="eq-bar"></div>
      <div class="eq-bar"></div>
    </div>
    <span id="musicTrackLabel" style="font-size: 0.88rem; font-weight: 700; color: #fff;">
      🎵 Música Ambiental
    </span>
  </div>

  <!-- Notificación para iniciar audio con interacción si el navegador lo bloquea -->
  <div class="music-prompt-banner" id="musicPromptBanner" onclick="startAudioExplicit()">
    🎶 Toca aquí para activar la música de fondo en vivo
  </div>
  
  <!-- Controles Superiores Derechos -->
  <div class="controls-top-right">
    <button class="btn-ctrl" onclick="prevSlide()">◀ Anterior</button>
    <button class="btn-ctrl" id="btnPause" onclick="togglePlayPause()">⏸ Pausar</button>
    <button class="btn-ctrl" onclick="nextSlide()">Siguiente ▶</button>
    <button class="btn-ctrl" onclick="toggleFullscreen()">⛶ Pantalla Completa</button>
  </div>

  <!-- Contenedor Central de Proyección -->
  <div id="slideshowContainer">
    <div class="empty-waiting" id="waitingState">
      <div style="font-size: 3.8rem; margin-bottom: 12px;">🎉 📸</div>
      <h2>¡Esperando los mejores momentos!</h2>
      <p style="font-size: 1.2rem; color: #cbd5e1; max-width: 580px; margin: 0 auto; line-height: 1.5;">
        Apunta tu celular al código QR en pantalla y sé el primero en salir en la fiesta.
      </p>
    </div>
  </div>

  <!-- Pie con autor del recuerdo actual -->
  <div class="slide-caption-bar" id="captionBar" style="display: none;">
    <span style="font-size: 1.2rem;">📸</span>
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
    let isPaused = false;
    let currentEventTheme = 'celebration';

    // Manejo de temporizador cinemático y barra de progreso estilo reel
    let slideTimeout = null;
    let progressAnimId = null;
    let slideStartTime = 0;
    let currentSlideDuration = 6000;

    // ========================================================
    // Motor de Música de Fondo Acorde a la Temática del Evento
    // ========================================================
    class EventThemeAudioEngine {
      constructor() {
        this.ctx = null;
        this.isPlaying = false;
        this.theme = 'celebration';
        this.timer = null;
        this.step = 0;
        this.masterGain = null;
      }

      initContext() {
        if (!this.ctx) {
          const AudioContextClass = window.AudioContext || window.webkitAudioContext;
          if (AudioContextClass) {
            this.ctx = new AudioContextClass();
            this.masterGain = this.ctx.createGain();
            this.masterGain.gain.setValueAtTime(0.7, this.ctx.currentTime);
            this.masterGain.connect(this.ctx.destination);
          }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
          this.ctx.resume();
        }
      }

      start(theme = 'celebration') {
        this.theme = theme;
        this.initContext();
        if (this.isPlaying) return;
        this.isPlaying = true;

        const banner = document.getElementById("musicPromptBanner");
        if (banner) banner.style.display = "none";

        const eq = document.getElementById("eqBars");
        if (eq) eq.classList.remove("paused");

        const label = document.getElementById("musicTrackLabel");
        if (label) label.textContent = `🎵 ${getThemeMusicTitle(this.theme)}`;

        this.scheduleMelodyLoop();
      }

      stop() {
        this.isPlaying = false;
        if (this.timer) {
          clearTimeout(this.timer);
          this.timer = null;
        }
        const eq = document.getElementById("eqBars");
        if (eq) eq.classList.add("paused");
        const label = document.getElementById("musicTrackLabel");
        if (label) label.textContent = "🔇 Música Pausada";
      }

      toggle(theme) {
        if (this.isPlaying) {
          this.stop();
        } else {
          this.start(theme || this.theme);
        }
      }

      duckAudio(isDucked) {
        if (!this.ctx || !this.masterGain) return;
        const now = this.ctx.currentTime;
        this.masterGain.gain.cancelScheduledValues(now);
        this.masterGain.gain.linearRampToValueAtTime(isDucked ? 0.15 : 0.7, now + 0.5);
      }

      scheduleMelodyLoop() {
        if (!this.isPlaying || !this.ctx) return;

        const themeHarmonies = {
          wedding: {
            chords: [
              [261.63, 329.63, 392.00, 523.25], // C
              [196.00, 246.94, 293.66, 392.00], // G
              [220.00, 261.63, 329.63, 440.00], // Am
              [174.61, 220.00, 261.63, 349.23]  // F
            ],
            stepTime: 3800,
            oscType: 'sine',
            filterFreq: 950
          },
          birthday: {
            chords: [
              [261.63, 329.63, 392.00, 659.25], // C
              [174.61, 220.00, 261.63, 349.23], // F
              [196.00, 246.94, 293.66, 392.00], // G
              [261.63, 329.63, 392.00, 523.25]  // C
            ],
            stepTime: 2800,
            oscType: 'triangle',
            filterFreq: 1400
          },
          celebration: {
            chords: [
              [174.61, 220.00, 261.63, 349.23], // F
              [196.00, 246.94, 293.66, 392.00], // G
              [164.81, 196.00, 246.94, 329.63], // Em
              [220.00, 261.63, 329.63, 440.00]  // Am
            ],
            stepTime: 3000,
            oscType: 'sine',
            filterFreq: 1200
          },
          elegant: {
            chords: [
              [146.83, 220.00, 261.63, 349.23], // Dm7
              [196.00, 246.94, 293.66, 349.23], // G7
              [130.81, 196.00, 246.94, 329.63], // Cmaj7
              [220.00, 261.63, 329.63, 392.00]  // Am7
            ],
            stepTime: 3600,
            oscType: 'triangle',
            filterFreq: 850
          }
        };

        const config = themeHarmonies[this.theme] || themeHarmonies.celebration;
        const currentChord = config.chords[this.step % config.chords.length];
        this.step++;

        const now = this.ctx.currentTime;
        const duration = (config.stepTime / 1000) * 1.15;

        currentChord.forEach((freq, i) => {
          const noteTime = now + (i * 0.16);
          const osc = this.ctx.createOscillator();
          const gain = this.ctx.createGain();
          const filter = this.ctx.createBiquadFilter();

          osc.type = config.oscType;
          osc.frequency.setValueAtTime(freq, noteTime);

          filter.type = 'lowpass';
          filter.frequency.setValueAtTime(config.filterFreq, noteTime);

          gain.gain.setValueAtTime(0.0001, noteTime);
          gain.gain.exponentialRampToValueAtTime(0.045, noteTime + 0.08);
          gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + duration);

          osc.connect(filter);
          filter.connect(gain);
          gain.connect(this.masterGain || this.ctx.destination);

          osc.start(noteTime);
          osc.stop(noteTime + duration + 0.1);
        });

        this.timer = setTimeout(() => {
          if (this.isPlaying) this.scheduleMelodyLoop();
        }, config.stepTime);
      }
    }

    const musicEngine = new EventThemeAudioEngine();

    function startAudioExplicit() {
      musicEngine.start(currentEventTheme);
    }

    function toggleMusic() {
      musicEngine.toggle(currentEventTheme);
    }

    function getThemeMusicTitle(theme) {
      switch(theme) {
        case 'wedding': return 'Romance Acústico 💍';
        case 'birthday': return 'Fiesta de Cumpleaños 🎂';
        case 'elegant': return 'Lounge & Smooth Piano ✨';
        default: return 'Alegría & Fiesta Pop 🎉';
      }
    }

    // Activar audio automáticamente en la primera interacción de usuario
    window.addEventListener("click", () => {
      if (!musicEngine.isPlaying) {
        musicEngine.start(currentEventTheme);
      }
    }, { once: true });

    // ========================================================
    // Carga de Datos y Sondeo en Vivo
    // ========================================================
    async function loadData() {
      if (!eventCode) return;
      try {
        const pin = urlParams.get('pin') || localStorage.getItem('moments_pin_' + eventCode) || '';
        const res = await fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}&pin=${encodeURIComponent(pin)}&mode=slideshow`);
        if (!res.ok) return;
        const data = await res.json();
        
        const theme = (data.event && data.event.theme) ? data.event.theme : 'celebration';
        currentEventTheme = theme;

        document.getElementById("eventTitle").textContent = data.event.title;
        document.getElementById("qrCornerImg").src = data.qr_url;

        const emojiEl = document.getElementById("themeEmoji");
        if (emojiEl) {
          emojiEl.textContent = theme === 'wedding' ? '💍' : (theme === 'birthday' ? '🎂' : (theme === 'elegant' ? '✨' : '🎉'));
        }

        if (!musicEngine.isPlaying) {
          const trackLabel = document.getElementById("musicTrackLabel");
          if (trackLabel) trackLabel.textContent = `🎵 ${getThemeMusicTitle(theme)}`;
        }

        const newMedia = data.media || [];
        if (newMedia.length > 0) {
          const hadZero = (mediaList.length === 0);
          const isNewItem = (mediaList.length > 0 && newMedia.length > mediaList.length);

          if (isNewItem) {
            showNewMediaToast(newMedia[0]);
          }

          mediaList = newMedia;

          const pill = document.getElementById("slideCounterPill");
          if (pill) {
            pill.style.display = "inline-block";
            pill.textContent = `${currentIndex >= 0 ? currentIndex + 1 : 1} / ${mediaList.length}`;
          }

          if (hadZero) {
            showSlide(0);
          }
        }
      } catch (e) {
        console.error("Error al cargar datos de proyector:", e);
      }
    }

    function showNewMediaToast(media) {
      const existing = document.getElementById("newMediaToast");
      if (existing) existing.remove();

      const toast = document.createElement("div");
      toast.id = "newMediaToast";
      toast.className = "new-media-toast";
      toast.innerHTML = `<span>🎉</span><span>¡Nuevo recuerdo de <strong>${escapeHtml(media.uploader_name)}</strong>!</span>`;
      document.body.appendChild(toast);
      setTimeout(() => { if (toast.parentNode) toast.remove(); }, 4500);
    }

    // ========================================================
    // Reproducción Cinemática Tipo Reel / Video
    // ========================================================
    function startSlideTimer(durationMs) {
      clearTimeout(slideTimeout);
      cancelAnimationFrame(progressAnimId);
      currentSlideDuration = durationMs;
      slideStartTime = performance.now();

      const bar = document.getElementById("storyProgressBar");

      function animFrame() {
        if (isPaused) {
          slideStartTime += 16;
          progressAnimId = requestAnimationFrame(animFrame);
          return;
        }
        const elapsed = performance.now() - slideStartTime;
        const pct = Math.min(100, (elapsed / currentSlideDuration) * 100);
        if (bar) bar.style.width = `${pct}%`;

        if (elapsed < currentSlideDuration) {
          progressAnimId = requestAnimationFrame(animFrame);
        }
      }

      progressAnimId = requestAnimationFrame(animFrame);

      slideTimeout = setTimeout(() => {
        if (!isPaused) {
          nextSlide();
        }
      }, durationMs);
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
      const counterPill = document.getElementById("slideCounterPill");

      if (waiting) waiting.style.display = "none";
      if (captionBar) captionBar.style.display = "flex";
      if (uploaderText) {
        uploaderText.innerHTML = `Momento de: <strong>${escapeHtml(media.uploader_name)}</strong> &nbsp;•&nbsp; ❤️ ${media.likes_count} &nbsp;•&nbsp; <span style="opacity:0.8; font-size:0.85rem;">${formatTimeAgo(media.created_at)}</span>`;
      }

      if (counterPill) {
        counterPill.textContent = `${currentIndex + 1} / ${mediaList.length}`;
      }

      if (media.file_type === "video") {
        backdrop.style.backgroundImage = media.thumb_url ? `url('${media.thumb_url}')` : "";
        container.innerHTML = `
          <video id="activeSlideVideo" class="slide-media slide-video active" autoplay playsinline loop style="max-height:84vh;">
            <source src="${media.file_url}" type="video/mp4">
          </video>
        `;
        const videoEl = document.getElementById("activeSlideVideo");
        if (videoEl) {
          musicEngine.duckAudio(true);
          videoEl.play().catch(() => {
            videoEl.muted = true;
            videoEl.play();
          });

          videoEl.onloadedmetadata = () => {
            const dur = (videoEl.duration && videoEl.duration > 2 && videoEl.duration < 40)
              ? (videoEl.duration * 1000)
              : 8000;
            startSlideTimer(dur);
          };
          videoEl.onended = () => {
            musicEngine.duckAudio(false);
            nextSlide();
          };
          startSlideTimer(10000);
        }
      } else {
        musicEngine.duckAudio(false);
        backdrop.style.backgroundImage = `url('${media.file_url}')`;
        container.innerHTML = `
          <img src="${media.file_url}" class="slide-media slide-photo active" alt="Recuerdo">
        `;
        // Fotos corren 6.5 segundos con animación Ken Burns
        startSlideTimer(6500);
      }
    }

    function nextSlide() {
      if (mediaList.length === 0) return;
      showSlide(currentIndex + 1);
    }

    function prevSlide() {
      if (mediaList.length === 0) return;
      showSlide(currentIndex - 1);
    }

    function togglePlayPause() {
      isPaused = !isPaused;
      document.getElementById("btnPause").textContent = isPaused ? "▶ Reanudar" : "⏸ Pausar";
    }

    function toggleFullscreen() {
      if (!musicEngine.isPlaying) {
        musicEngine.start(currentEventTheme);
      }
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        document.exitFullscreen().catch(() => {});
      }
    }

    // Atajos de teclado para controlar la pantalla gigante
    window.addEventListener("keydown", (e) => {
      if (e.code === "Space") {
        e.preventDefault();
        togglePlayPause();
      } else if (e.code === "ArrowRight") {
        nextSlide();
      } else if (e.code === "ArrowLeft") {
        prevSlide();
      } else if (e.code === "KeyF") {
        toggleFullscreen();
      } else if (e.code === "KeyM") {
        toggleMusic();
      }
    });

    function formatTimeAgo(isoString) {
      if (!isoString) return "";
      const date = new Date(isoString.replace(/-/g, "/"));
      const now = new Date();
      const diffSec = Math.floor((now - date) / 1000);
      if (diffSec < 60) return "hace unos segundos";
      const diffMin = Math.floor(diffSec / 60);
      if (diffMin < 60) return `hace ${diffMin} min`;
      const diffHours = Math.floor(diffMin / 60);
      if (diffHours < 24) return `hace ${diffHours} h`;
      return `hace ${Math.floor(diffHours / 24)} d`;
    }

    function escapeHtml(text) {
      if (!text) return "";
      const div = document.createElement("div");
      div.innerText = text;
      return div.innerHTML;
    }

    // Iniciar carga inmediata y sondeo en vivo cada 6 segundos
    loadData().then(() => {
      setInterval(loadData, 6000);
    });
  </script>
</body>
</html>
