<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Álbum del Evento - Moments Drive</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="container">
    <!-- Barra Superior -->
    <header class="header" style="margin-bottom: 12px; padding-bottom: 10px;">
      <a href="index.php" class="logo" style="font-size: 1.1rem;">
        <span class="logo-icon">📸</span> Moments Drive
      </a>
      <!-- Identidad del Invitado -->
      <div class="guest-badge" onclick="promptGuestName(true)">
        <span>👤</span>
        <span id="guestNameDisplay">Tu Nombre</span>
      </div>
    </header>

    <!-- Encabezado del Evento -->
    <section class="event-hero">
      <h1 class="event-title" id="eventTitleText">Cargando celebración...</h1>
      <div class="event-meta">
        <span id="eventDateText"></span>
        <span id="eventLocationText"></span>
      </div>
      <p id="eventDescriptionText" style="display:none; color:#cbd5e1; font-size:0.95rem; max-width:600px; margin:0 auto 12px;"></p>
      
      <!-- Enlaces rápidos para el organizador -->
      <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
        <a id="linkPoster" href="#" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">
          🖨️ Cartel QR
        </a>
        <a id="linkSlideshow" href="#" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">
          📺 Pantalla en Vivo
        </a>
        <button onclick="downloadAllZip()" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">
          📦 Descargar ZIP
        </button>
      </div>
    </section>

    <!-- Botones Principales de Captura y Subida -->
    <div class="action-buttons-grid">
      <!-- Botón 1: Cámara en Vivo -->
      <label for="cameraInput" class="btn-camera" style="cursor: pointer;">
        <span class="icon">📸</span>
        <span>Tomar Foto / Video</span>
        <span style="font-size: 0.75rem; opacity: 0.9; font-weight: normal;">Abre la cámara al instante</span>
      </label>
      <input type="file" id="cameraInput" accept="image/*,video/*" capture="environment" style="display: none;">

      <!-- Botón 2: Galería de Fotos -->
      <label for="galleryInput" class="btn-gallery" style="cursor: pointer;">
        <span class="icon">🖼️</span>
        <span>Subir de Galería</span>
        <span style="font-size: 0.75rem; opacity: 0.9; font-weight: normal;">Elige varias fotos del cel</span>
      </label>
      <input type="file" id="galleryInput" accept="image/*,video/*" multiple style="display: none;">
    </div>

    <!-- Indicador de Progreso de Subida -->
    <div class="upload-progress-container" id="uploadProgressCard">
      <div class="upload-progress-header">
        <span id="uploadProgressStatus">Subiendo recuerdo...</span>
        <span id="uploadProgressPercent">0%</span>
      </div>
      <div class="progress-bar-outer">
        <div class="progress-bar-inner" id="uploadProgressBar"></div>
      </div>
    </div>

    <!-- Barra de Herramientas y Filtros -->
    <div class="tools-bar">
      <div class="filter-tabs">
        <button class="tab-btn active" data-filter="all" onclick="setFilter('all')">Todos</button>
        <button class="tab-btn" data-filter="image" onclick="setFilter('image')">Fotos</button>
        <button class="tab-btn" data-filter="video" onclick="setFilter('video')">Videos</button>
      </div>
      <div id="mediaCountText" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
        0 recuerdos compartidos
      </div>
    </div>

    <!-- Galería en Vivo -->
    <div class="gallery-grid" id="galleryGrid"></div>

    <!-- Estado Vacío -->
    <div class="empty-gallery" id="emptyGalleryState" style="display: none;">
      <span class="empty-icon">📷✨</span>
      <h3 style="font-size: 1.2rem; color: #fff; margin-bottom: 6px;">Sé el primero en compartir</h3>
      <p style="font-size: 0.95rem; margin-bottom: 18px;">Usa los botones de arriba para capturar y compartir los mejores momentos de la fiesta.</p>
    </div>
  </div>

  <!-- Visor Lightbox a Pantalla Completa -->
  <div class="modal-overlay" id="lightboxModal" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
      <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
      <div id="lightboxMediaContainer"></div>
      
      <div class="lightbox-controls">
        <span id="lightboxUploader" style="font-size: 0.9rem; font-weight: 600;"></span>
        <div style="display: flex; gap: 8px;">
          <a id="lightboxDownloadBtn" href="#" download class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">
            ⬇️ Guardar
          </a>
          <button onclick="deleteCurrentLightboxMedia()" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" title="Eliminar (requiere PIN)">
            🗑️
          </button>
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; width: 100%; margin-top: 10px;">
        <button class="btn btn-secondary" onclick="prevLightbox()" style="padding: 8px 16px;">◀ Anterior</button>
        <button class="btn btn-secondary" onclick="nextLightbox()" style="padding: 8px 16px;">Siguiente ▶</button>
      </div>
    </div>
  </div>

  <!-- Barra de Acciones Flotante Inferior en Móvil -->
  <div class="mobile-bottom-bar">
    <label for="cameraInput" class="bottom-action-btn" style="cursor: pointer;">
      <span class="btn-icon">📸</span>
      <span>Cámara</span>
    </label>
    <label for="galleryInput" class="bottom-action-btn" style="cursor: pointer;">
      <span class="btn-icon">📁</span>
      <span>Galería</span>
    </label>
    <button class="bottom-action-btn" onclick="loadEventData()">
      <span class="btn-icon">🔄</span>
      <span>Actualizar</span>
    </button>
    <button class="bottom-action-btn" onclick="promptGuestName(true)">
      <span class="btn-icon">👤</span>
      <span>Mi Nombre</span>
    </button>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const eventCode = urlParams.get('code') || '';

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
        const entered = prompt("¿Cuál es tu nombre? (Para que sepan quién tomó la foto):", current || "");
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
        badge.textContent = getGuestName() || "Invitado (Clic para cambiar)";
      }
    }

    async function loadEventData() {
      if (!eventCode) {
        alert("Código de evento no especificado.");
        window.location.href = "index.php";
        return;
      }

      try {
        const res = await fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}`);
        if (!res.ok) {
          document.body.innerHTML = `<div class="container" style="text-align:center; padding:50px 20px;">
            <h2>Evento no encontrado</h2>
            <p style="color:#94a3b8; margin:16px 0;">El enlace no corresponde a un evento activo.</p>
            <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
          </div>`;
          return;
        }
        const data = await res.json();
        renderEventHeader(data.event);
        currentMediaList = data.media || [];
        renderGallery();
      } catch (err) {
        console.error("Error:", err);
      }
    }

    function renderEventHeader(event) {
      document.getElementById("eventTitleText").textContent = event.title;
      if (event.event_date) document.getElementById("eventDateText").innerHTML = `📅 ${escapeHtml(event.event_date)}`;
      if (event.location) document.getElementById("eventLocationText").innerHTML = `📍 ${escapeHtml(event.location)}`;
      
      const descEl = document.getElementById("eventDescriptionText");
      if (event.description) {
        descEl.textContent = event.description;
        descEl.style.display = "block";
      }

      document.getElementById("linkPoster").href = `poster.php?code=${event.code}`;
      document.getElementById("linkSlideshow").href = `slideshow.php?code=${event.code}`;
    }

    function setFilter(filter) {
      currentFilter = filter;
      document.querySelectorAll(".tab-btn").forEach(btn => {
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

    // Subida de archivos
    document.getElementById("cameraInput").addEventListener("change", (e) => handleFilesSelected(e.target.files));
    document.getElementById("galleryInput").addEventListener("change", (e) => handleFilesSelected(e.target.files));

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
        progressStatus.textContent = `Subiendo recuerdo ${i + 1} de ${totalFiles}... (${file.name})`;

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

      progressStatus.textContent = "¡Recuerdos subidos con éxito! 🎉";
      setTimeout(() => {
        progressCard.style.display = "none";
        progressBar.style.width = "0%";
      }, 2500);

      document.getElementById("cameraInput").value = "";
      document.getElementById("galleryInput").value = "";
      await loadEventData();
    }

    function uploadSingleFile(file, uploader, onProgress) {
      return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append("file", file);
        formData.append("code", eventCode);
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

        xhr.onerror = () => reject(new Error("Error de red"));
        xhr.send(formData);
      });
    }

    // Lightbox
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
        container.innerHTML = `<video class="lightbox-media" controls autoplay playsinline style="max-height:75vh; width:100%;">
          <source src="${media.file_url}" type="video/mp4">
        </video>`;
      } else {
        container.innerHTML = `<img src="${media.file_url}" class="lightbox-media" alt="Foto ampliada">`;
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
      const pin = prompt("Ingresa el PIN de anfitrión para descargar todo el álbum en ZIP:");
      if (pin === null) return;
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
    // Actualización automática cada 8s
    setInterval(loadEventData, 8000);
  </script>
</body>
</html>
