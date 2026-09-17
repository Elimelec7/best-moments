<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Experience your best moments with us! - Álbum del Evento</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="container">
    <!-- Barra Superior de Marca -->
    <header class="header">
      <a href="index.php" class="brand-wrapper">
        <div class="brand-icon" style="width: 38px; height: 38px; font-size: 1.2rem;">✨</div>
        <div class="brand-text">
          <span class="brand-title" style="font-size: 1rem;">Experience your best moments with us!</span>
          <span class="brand-slogan">Álbum Compartido en Vivo</span>
        </div>
      </a>
      
      <!-- Identidad del Invitado -->
      <div class="guest-pill" onclick="promptGuestName(true)">
        <span>👤</span>
        <span id="guestNameDisplay">Mi Nombre</span>
        <span style="font-size: 0.7rem; opacity: 0.8;">✏️</span>
      </div>
    </header>

    <!-- Tarjeta de Bloqueo por PIN de Seguridad (Si se ingresa sin PIN o sin QR) -->
    <div class="glass-card" id="pinGateCard" style="display: none; text-align: center; max-width: 480px; margin: 30px auto 40px; padding: 40px 24px; border-color: var(--primary);">
      <div style="font-size: 3.2rem; margin-bottom: 12px;">🔒</div>
      <h2 style="font-size: 1.8rem; font-weight: 900; color: #fff; margin-bottom: 8px;">Álbum Protegido</h2>
      <p id="pinGateTitle" style="color: var(--primary); font-size: 1.2rem; font-weight: 800; margin-bottom: 12px;"></p>
      <p style="color: #cbd5e1; font-size: 0.95rem; margin-bottom: 24px; line-height: 1.5;">
        Esta celebración es privada. Si estás en la fiesta, <strong>escanea el código QR</strong> de tu mesa para entrar directo, o escribe el <strong>PIN de anfitrión</strong> proporcionado.
      </p>
      <form id="pinUnlockForm" onsubmit="handlePinSubmit(event)">
        <div class="form-group" style="margin-bottom: 16px;">
          <input type="password" id="inputPinUnlock" class="form-input" placeholder="PIN de 4 dígitos" maxlength="12" style="text-align: center; font-size: 1.4rem; letter-spacing: 6px; font-weight: 800;" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnUnlockSubmit">
          🔓 Desbloquear Álbum
        </button>
      </form>
      <div id="pinErrorAlert" style="display: none; color: #f87171; font-weight: 700; margin-top: 14px; background: rgba(239, 68, 68, 0.15); padding: 10px; border-radius: 8px;">
        ❌ PIN incorrecto. Por favor verifícalo con el anfitrión.
      </div>
      <div style="margin-top: 24px;">
        <a href="index.php" style="color: #94a3b8; font-size: 0.88rem; text-decoration: underline;">← Volver al Inicio</a>
      </div>
    </div>

    <!-- Contenido Protegido del Álbum (Visible solo con PIN válido o por QR) -->
    <div id="eventMainContent" style="display: none;">
      <!-- Perfil Temático Dinámico y Hermoso del Evento -->
      <section class="event-profile-card theme-celebration" id="eventProfileCard">
        <!-- Sello / Avatar animado según temática -->
        <div class="profile-avatar-seal" id="profileAvatarSeal">🎉</div>
        <div>
          <span class="hero-pill" id="eventPillBadge">🎉 Celebración Especial</span>
        </div>
        <h1 class="card-title" id="eventTitleText" style="font-size: 2.1rem; justify-content: center; margin: 10px 0 8px; font-weight: 900; letter-spacing: -0.02em;">
          Cargando evento...
        </h1>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; color: #cbd5e1; font-size: 0.92rem; margin-bottom: 12px;">
          <span id="eventDateText"></span>
          <span id="eventLocationText"></span>
          <span id="eventHeaderCount" style="color: var(--primary); font-weight: 700;">📸 0 recuerdos</span>
        </div>
        <div id="eventDescriptionWrapper" style="display: none; max-width: 620px; margin: 0 auto 16px; background: rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 12px 18px; border: 1px solid rgba(255, 255, 255, 0.12);">
          <p id="eventDescriptionText" style="color: #f1f5f9; font-size: 0.96rem; line-height: 1.5; font-style: italic; margin: 0;"></p>
        </div>
        
        <!-- Enlaces Rápidos: Cartel QR solo para anfitrión, Pantalla y ZIP para todos -->
        <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 14px; align-items: center;">
          <span id="hostActions" style="display: none;">
            <a id="linkPoster" href="#" target="_blank" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.84rem; box-shadow: 0 4px 15px var(--primary-glow);">
              🖨️ Cartel QR Mesas
            </a>
          </span>
          <a id="linkSlideshow" href="#" target="_blank" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.84rem;">
            📺 Pantalla / TV en Vivo
          </a>
          <button onclick="downloadAllZip()" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.84rem;">
            📦 Descargar Todo (ZIP)
          </button>
        </div>

        <!-- Enlace discreto para activar modo anfitrión si se accede desde otro navegador -->
        <div id="hostClaimSection" style="margin-top: 14px; font-size: 0.8rem;">
          <a href="javascript:void(0)" onclick="claimHostRole()" id="hostClaimLink" style="color: #94a3b8; text-decoration: underline; opacity: 0.85;">
            🔑 ¿Eres el anfitrión de esta celebración?
          </a>
        </div>
      </section>

      <!-- Botones de Acción Gráficos Gigantes Separados (Cámara Foto, Cámara Video, Galería) -->
      <div class="action-cards-grid-3">
        <!-- Botón 1: Tomar Foto Directo (Abre la cámara de fotos nativa) -->
        <label for="cameraPhotoInput" class="action-card action-camera">
          <span class="card-icon">📸</span>
          <span class="card-main-text">Tomar Foto</span>
          <span class="card-sub-text">Cámara al instante</span>
        </label>
        <input type="file" id="cameraPhotoInput" accept="image/*" capture="environment" style="display: none;">

        <!-- Botón 2: Grabar Video Directo (Abre la filmadora de video nativa) -->
        <label for="cameraVideoInput" class="action-card action-video">
          <span class="card-icon">🎥</span>
          <span class="card-main-text">Grabar Video</span>
          <span class="card-sub-text">Filma el momento</span>
        </label>
        <input type="file" id="cameraVideoInput" accept="video/*" capture="environment" style="display: none;">

        <!-- Botón 3: Subir de Galería (Selecciona fotos y videos del celular) -->
        <label for="galleryInput" class="action-card action-gallery">
          <span class="card-icon">🖼️</span>
          <span class="card-main-text">Galería</span>
          <span class="card-sub-text">Sube varias fotos</span>
        </label>
        <input type="file" id="galleryInput" accept="image/*,video/*" multiple style="display: none;">
      </div>

      <!-- Barra de Progreso Dinámica durante la subida -->
      <div class="upload-progress-container" id="uploadProgressCard">
        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 0.95rem;">
          <span id="uploadProgressStatus">Subiendo recuerdo...</span>
          <span id="uploadProgressPercent" style="color: var(--primary);">0%</span>
        </div>
        <div class="progress-bar-outer">
          <div class="progress-bar-inner" id="uploadProgressBar"></div>
        </div>
      </div>

      <!-- Barra de Filtros y Contador de Recuerdos -->
      <div class="stats-bar">
        <div class="filter-pills">
          <button class="filter-btn active" data-filter="all" onclick="setFilter('all')">Todos</button>
          <button class="filter-btn" data-filter="image" onclick="setFilter('image')">Fotos</button>
          <button class="filter-btn" data-filter="video" onclick="setFilter('video')">Videos</button>
        </div>
        <div id="mediaCountText" style="font-size: 0.88rem; color: #cbd5e1; font-weight: 700;">
          0 recuerdos compartidos
        </div>
      </div>

      <!-- Cuadrícula Dinámica de la Galería -->
      <div class="gallery-grid" id="galleryGrid"></div>

      <!-- Estado Vacío Invitador -->
      <div class="glass-card" id="emptyGalleryState" style="display: none; text-align: center; padding: 48px 20px;">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">📷 ✨</div>
        <h3 style="font-size: 1.3rem; color: #fff; font-weight: 800; margin-bottom: 8px;">
          ¡Sé el primero en compartir un momento!
        </h3>
        <p style="color: #94a3b8; max-width: 480px; margin: 0 auto 20px; font-size: 0.95rem;">
          Toca los botones superiores para activar tu cámara o subir fotos desde tu celular. ¡Todos los invitados podrán verlos en vivo!
        </p>
      </div>
    </div>
  </div>

  <!-- Visor Lightbox Pantalla Completa -->
  <div class="modal-overlay" id="lightboxModal" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
      <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
      <div id="lightboxMediaContainer"></div>
      
      <div class="lightbox-controls">
        <span id="lightboxUploader" style="font-size: 0.92rem; font-weight: 700; color: #f8fafc;"></span>
        <div style="display: flex; gap: 8px;">
          <a id="lightboxDownloadBtn" href="#" download class="btn btn-secondary" style="padding: 7px 14px; font-size: 0.84rem;">
            ⬇️ Guardar
          </a>
          <button onclick="deleteCurrentLightboxMedia()" class="btn btn-danger" style="padding: 7px 14px; font-size: 0.84rem;" title="Eliminar (requiere PIN)">
            🗑️
          </button>
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; width: 100%; margin-top: 14px;">
        <button class="btn btn-secondary" onclick="prevLightbox()" style="padding: 10px 18px;">◀ Anterior</button>
        <button class="btn btn-secondary" onclick="nextLightbox()" style="padding: 10px 18px;">Siguiente ▶</button>
      </div>
    </div>
  </div>

  <!-- Barra Flotante Inferior en Pantallas Móviles -->
  <div class="mobile-bottom-bar" id="mobileBottomBar" style="display: none;">
    <label for="cameraPhotoInput" class="bottom-action-btn" style="cursor: pointer;">
      <span class="btn-icon">📸</span>
      <span>Foto</span>
    </label>
    <label for="cameraVideoInput" class="bottom-action-btn" style="cursor: pointer;">
      <span class="btn-icon">🎥</span>
      <span>Video</span>
    </label>
    <label for="galleryInput" class="bottom-action-btn" style="cursor: pointer;">
      <span class="btn-icon">📁</span>
      <span>Galería</span>
    </label>
    <button class="bottom-action-btn" onclick="loadEventData()">
      <span class="btn-icon">🔄</span>
      <span>Refrescar</span>
    </button>
    <button class="bottom-action-btn" onclick="promptGuestName(true)">
      <span class="btn-icon">👤</span>
      <span>Mi Nombre</span>
    </button>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const eventCode = urlParams.get('code') || '';
    
    // Si viene en el enlace (típico al escanear el Código QR), guardar en memoria del celular
    const urlPin = urlParams.get('pin');
    if (urlPin) {
      localStorage.setItem('moments_pin_' + eventCode, urlPin.trim());
    }

    function getEventPin() {
      return localStorage.getItem('moments_pin_' + eventCode) || '';
    }

    function setEventPin(pin) {
      if (pin) localStorage.setItem('moments_pin_' + eventCode, pin.trim());
    }

    let currentMediaList = [];
    let currentFilter = "all";
    let lightboxIndex = -1;

    function getGuestName() {
      return localStorage.getItem("moments_guest_name") || "";
    }

    function setGuestName(name) {
      if (name && name.trim()) {
        localStorage.setItem("moments_guest_name", name.trim());
      }
    }

    function promptGuestName(force = false) {
      let current = getGuestName();
      if (!current || force) {
        const entered = prompt("¿Cuál es tu nombre? (Para que aparezca en tus fotos):", current || "");
        if (entered && entered.trim()) {
          setGuestName(entered);
          updateGuestBadge();
          return entered.trim();
        }
      }
      return current || "Invitado";
    }

    function updateGuestBadge() {
      const badge = document.getElementById("guestNameDisplay");
      if (badge) {
        badge.textContent = getGuestName() || "Mi Nombre";
      }
    }

    async function loadEventData() {
      if (!eventCode) {
        alert("Código de evento no especificado.");
        window.location.href = "index.php";
        return;
      }

      const pin = getEventPin();

      try {
        const res = await fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}&pin=${encodeURIComponent(pin)}`);
        if (!res.ok) {
          document.body.innerHTML = `<div class="container" style="text-align:center; padding:60px 20px;">
            <h2 style="font-size:2rem; margin-bottom:12px;">Evento no encontrado</h2>
            <p style="color:#94a3b8; margin-bottom:20px;">El enlace o código QR no corresponde a una celebración activa.</p>
            <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
          </div>`;
          return;
        }

        const data = await res.json();

        // Si el evento está bloqueado por PIN
        if (data.locked) {
          document.getElementById("pinGateCard").style.display = "block";
          document.getElementById("eventMainContent").style.display = "none";
          document.getElementById("mobileBottomBar").style.display = "none";
          document.getElementById("pinGateTitle").textContent = data.event.title;
          return;
        }

        // Si está desbloqueado
        document.getElementById("pinGateCard").style.display = "none";
        document.getElementById("eventMainContent").style.display = "block";
        document.getElementById("mobileBottomBar").style.display = "flex";

        renderEventHeader(data.event);
        currentMediaList = data.media || [];
        renderGallery();
      } catch (err) {
        console.error("Error al cargar datos:", err);
      }
    }

    async function handlePinSubmit(e) {
      e.preventDefault();
      const inputPin = document.getElementById("inputPinUnlock").value.trim();
      const btn = document.getElementById("btnUnlockSubmit");
      btn.disabled = true;
      btn.textContent = "Verificando PIN...";

      setEventPin(inputPin);

      try {
        const res = await fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}&pin=${encodeURIComponent(inputPin)}`);
        const data = await res.json();
        btn.disabled = false;
        btn.textContent = "🔓 Desbloquear Álbum";

        if (data.locked) {
          document.getElementById("pinErrorAlert").style.display = "block";
          document.getElementById("inputPinUnlock").select();
        } else {
          document.getElementById("pinErrorAlert").style.display = "none";
          document.getElementById("pinGateCard").style.display = "none";
          document.getElementById("eventMainContent").style.display = "block";
          document.getElementById("mobileBottomBar").style.display = "flex";

          renderEventHeader(data.event);
          currentMediaList = data.media || [];
          renderGallery();
        }
      } catch (err) {
        btn.disabled = false;
        btn.textContent = "🔓 Desbloquear Álbum";
        alert("Error de conexión al verificar el PIN.");
      }
    }

    const themeConfigs = {
      wedding: { seal: '💍', badge: '💍 Boda de Ensueño', class: 'theme-wedding' },
      birthday: { seal: '🎂', badge: '🎂 ¡Feliz Cumpleaños!', class: 'theme-birthday' },
      celebration: { seal: '🎉', badge: '🎉 Gran Celebración', class: 'theme-celebration' },
      elegant: { seal: '✨', badge: '✨ Gala & Aniversario', class: 'theme-elegant' }
    };

    function renderEventHeader(event) {
      const themeKey = event.theme || 'celebration';
      const themeCfg = themeConfigs[themeKey] || themeConfigs.celebration;

      const card = document.getElementById("eventProfileCard");
      if (card) card.className = "event-profile-card " + themeCfg.class;

      const seal = document.getElementById("profileAvatarSeal");
      if (seal) seal.textContent = themeCfg.seal;

      const pill = document.getElementById("eventPillBadge");
      if (pill) pill.textContent = themeCfg.badge;

      document.getElementById("eventTitleText").textContent = event.title;
      if (event.event_date) document.getElementById("eventDateText").innerHTML = `📅 ${escapeHtml(event.event_date)}`;
      if (event.location) document.getElementById("eventLocationText").innerHTML = `📍 ${escapeHtml(event.location)}`;
      
      const countEl = document.getElementById("eventHeaderCount");
      if (countEl) countEl.textContent = `📸 ${currentMediaList.length} recuerdos`;

      const descWrapper = document.getElementById("eventDescriptionWrapper");
      const descEl = document.getElementById("eventDescriptionText");
      if (event.description && event.description.trim()) {
        descEl.textContent = `“${event.description}”`;
        descWrapper.style.display = "block";
      } else {
        descWrapper.style.display = "none";
      }

      const pinParam = encodeURIComponent(getEventPin());
      document.getElementById("linkPoster").href = `poster.php?code=${event.code}&pin=${pinParam}`;
      document.getElementById("linkSlideshow").href = `slideshow.php?code=${event.code}&pin=${pinParam}`;

      // Control de visibilidad del botón "Cartel QR Mesas": Solo visible para el anfitrión
      updateHostVisibility();
    }

    function updateHostVisibility() {
      const isHost = localStorage.getItem('moments_host_' + eventCode) === 'true';
      const hostSpan = document.getElementById("hostActions");
      const hostClaim = document.getElementById("hostClaimSection");
      if (hostSpan) {
        hostSpan.style.display = isHost ? "inline-block" : "none";
      }
      if (hostClaim) {
        hostClaim.style.display = isHost ? "none" : "block";
      }
    }

    function claimHostRole() {
      const currentPin = getEventPin();
      const entered = prompt("Ingresa el PIN de anfitrión para activar las herramientas de administración en este dispositivo:", currentPin || "");
      if (!entered) return;

      fetch(`api.php?action=verify_pin&code=${encodeURIComponent(eventCode)}&pin=${encodeURIComponent(entered.trim())}`)
        .then(r => r.json())
        .then(data => {
          if (data.valid) {
            localStorage.setItem('moments_host_' + eventCode, 'true');
            setEventPin(entered.trim());
            updateHostVisibility();
            alert("✨ ¡Modo Anfitrión activado! El botón Cartel QR Mesas ya está disponible.");
          } else {
            alert("❌ PIN incorrecto.");
          }
        })
        .catch(() => alert("Error de conexión al verificar el PIN."));
    }

    function setFilter(filter) {
      currentFilter = filter;
      document.querySelectorAll(".filter-btn").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.filter === filter);
      });
      renderGallery();
    }

    function renderGallery() {
      const container = document.getElementById("galleryGrid");
      const emptyState = document.getElementById("emptyGalleryState");
      const counterEl = document.getElementById("mediaCountText");

      let filtered = currentMediaList;
      if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
      if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

      if (counterEl) counterEl.textContent = `${currentMediaList.length} recuerdos compartidos`;

      if (filtered.length === 0) {
        container.innerHTML = "";
        emptyState.style.display = "block";
        return;
      }

      emptyState.style.display = "none";
      container.innerHTML = filtered.map((media, index) => {
        const isVideo = media.file_type === "video";
        const timeAgo = formatTimeAgo(media.created_at);
        return `
          <div class="media-card" onclick="openLightbox(${index})">
            <img src="${media.thumb_url}" alt="Recuerdo" loading="lazy" />
            ${isVideo ? `<div class="video-badge">▶ Video</div>` : ''}
            <div class="media-overlay">
              <div class="uploader-info">
                <span class="uploader-name">${escapeHtml(media.uploader_name)}</span>
                <span class="media-time">${timeAgo}</span>
              </div>
              <button class="like-btn" onclick="event.stopPropagation(); handleLike(${media.id})">
                ❤️ <span>${media.likes_count}</span>
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    async function handleLike(mediaId) {
      try {
        const res = await fetch(`api.php?action=like&media_id=${mediaId}`, { method: "POST" });
        if (res.ok) {
          const data = await res.json();
          const item = currentMediaList.find(m => m.id == mediaId);
          if (item) {
            item.likes_count = data.likes_count;
            renderGallery();
          }
        }
      } catch (err) {}
    }

    // Subida de Archivos con Barra de Progreso y PIN de seguridad (Foto directa, Video directo, Galería)
    const photoInput = document.getElementById("cameraPhotoInput");
    const videoInput = document.getElementById("cameraVideoInput");
    const galleryInput = document.getElementById("galleryInput");

    if (photoInput) photoInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
    if (videoInput) videoInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
    if (galleryInput) galleryInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));

    async function handleFilesSelected(fileList) {
      if (!fileList || fileList.length === 0) return;

      let uploader = getGuestName();
      if (!uploader) uploader = promptGuestName(true);

      const progressCard = document.getElementById("uploadProgressCard");
      const progressBar = document.getElementById("uploadProgressBar");
      const progressPercent = document.getElementById("uploadProgressPercent");
      const progressStatus = document.getElementById("uploadProgressStatus");

      progressCard.style.display = "block";
      const totalFiles = fileList.length;
      let completed = 0;

      for (let i = 0; i < totalFiles; i++) {
        const file = fileList[i];
        progressStatus.textContent = `Subiendo momento ${i + 1} de ${totalFiles}... (${file.name})`;

        try {
          await uploadSingleFile(file, uploader, (percent) => {
            const overall = Math.round(((completed + (percent / 100)) / totalFiles) * 100);
            progressBar.style.width = `${overall}%`;
            progressPercent.textContent = `${overall}%`;
          });
          completed++;
        } catch (err) {
          alert(`Error al subir ${file.name}: ${err.message}`);
        }
      }

      progressStatus.textContent = "¡Momento compartido con éxito! 🎉✨";
      setTimeout(() => {
        progressCard.style.display = "none";
        progressBar.style.width = "0%";
      }, 2200);

      if (photoInput) photoInput.value = "";
      if (videoInput) videoInput.value = "";
      if (galleryInput) galleryInput.value = "";
      await loadEventData();
    }

    function uploadSingleFile(file, uploader, onProgress) {
      return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append("file", file);
        formData.append("code", eventCode);
        formData.append("pin", getEventPin());
        formData.append("uploader_name", uploader);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "api.php?action=upload", true);

        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            onProgress(Math.round((e.loaded / e.total) * 100));
          }
        };

        xhr.onload = () => {
          if (xhr.status >= 200 && xhr.status < 300) {
            resolve(JSON.parse(xhr.responseText));
          } else {
            try {
              reject(new Error(JSON.parse(xhr.responseText).error || "Error al subir"));
            } catch (_) {
              reject(new Error("Error del servidor"));
            }
          }
        };

        xhr.onerror = () => reject(new Error("Error de conexión"));
        xhr.send(formData);
      });
    }

    // Lightbox Visor
    function openLightbox(index) {
      let filtered = currentMediaList;
      if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
      if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

      if (index < 0 || index >= filtered.length) return;
      lightboxIndex = index;
      const media = filtered[lightboxIndex];

      const modal = document.getElementById("lightboxModal");
      const container = document.getElementById("lightboxMediaContainer");
      const uploaderSpan = document.getElementById("lightboxUploader");
      const downloadLink = document.getElementById("lightboxDownloadBtn");

      if (media.file_type === "video") {
        container.innerHTML = `<video class="lightbox-media" controls autoplay playsinline style="max-height:74vh; width:100%;">
          <source src="${media.file_url}" type="video/mp4">
        </video>`;
      } else {
        container.innerHTML = `<img src="${media.file_url}" class="lightbox-media" alt="Foto">`;
      }

      uploaderSpan.textContent = `Foto de: ${media.uploader_name} (${formatTimeAgo(media.created_at)})`;
      downloadLink.href = media.file_url;
      downloadLink.download = media.original_filename || "recuerdo";

      modal.classList.add("active");
    }

    function closeLightbox() {
      document.getElementById("lightboxModal").classList.remove("active");
      document.getElementById("lightboxMediaContainer").innerHTML = "";
    }

    function nextLightbox() {
      let filtered = currentMediaList;
      if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
      if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");
      openLightbox(lightboxIndex < filtered.length - 1 ? lightboxIndex + 1 : 0);
    }

    function prevLightbox() {
      let filtered = currentMediaList;
      if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
      if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");
      openLightbox(lightboxIndex > 0 ? lightboxIndex - 1 : filtered.length - 1);
    }

    async function downloadAllZip() {
      let pin = getEventPin();
      if (!pin) {
        pin = prompt("Ingresa el PIN de anfitrión para descargar todo el álbum en ZIP:");
      }
      if (!pin) return;
      window.open(`api.php?action=download_zip&code=${encodeURIComponent(eventCode)}&pin=${encodeURIComponent(pin)}`, "_blank");
    }

    async function deleteCurrentLightboxMedia() {
      let filtered = currentMediaList;
      if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
      if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

      const media = filtered[lightboxIndex];
      if (!media) return;

      const pin = prompt("Ingresa el PIN de anfitrión para confirmar la eliminación:");
      if (!pin) return;

      const res = await fetch(`api.php?action=delete`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `media_id=${media.id}&pin=${encodeURIComponent(pin)}`
      });
      const data = await res.json();
      if (res.ok && data.success) {
        alert("Recuerdo eliminado.");
        closeLightbox();
        await loadEventData();
      } else {
        alert(data.error || "PIN incorrecto");
      }
    }

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

    updateGuestBadge();
    loadEventData();
    setInterval(loadEventData, 8000);
  </script>
</body>
</html>
