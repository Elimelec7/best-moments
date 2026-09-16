/**
 * Moments Drive - Lógica Frontend
 */

// Utilidad para obtener el nombre del invitado
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
    const name = getGuestName() || "Invitado (Clic para cambiar)";
    badge.textContent = name;
  }
}

// ==========================================
// PÁGINA PRINCIPAL: CREAR EVENTO
// ==========================================

function initHomePage() {
  const form = document.getElementById("createEventForm");
  const resultCard = document.getElementById("eventResultCard");
  const recentEventsContainer = document.getElementById("recentEventsList");

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector("button[type='submit']");
      submitBtn.disabled = true;
      submitBtn.textContent = "Creando evento y generando QR...";

      const payload = {
        title: document.getElementById("eventTitle").value,
        event_date: document.getElementById("eventDate").value,
        location: document.getElementById("eventLocation").value,
        description: document.getElementById("eventDescription").value,
        admin_pin: document.getElementById("eventPin").value || "1234",
        custom_code: document.getElementById("eventCustomCode").value,
        theme: document.getElementById("eventTheme").value
      };

      try {
        const res = await fetch("/api/events", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.ok) {
          form.style.display = "none";
          resultCard.style.display = "block";
          
          document.getElementById("createdEventTitle").textContent = data.event.title;
          document.getElementById("createdEventQrImg").src = data.qr_url;
          document.getElementById("createdEventUrlInput").value = data.target_url;
          
          document.getElementById("btnOpenEvent").href = `/e/${data.event.code}`;
          document.getElementById("btnPrintPoster").href = `/e/${data.event.code}/poster`;
          document.getElementById("btnOpenSlideshow").href = `/e/${data.event.code}/slideshow`;

          resultCard.scrollIntoView({ behavior: "smooth" });
        } else {
          alert(data.detail || "Error al crear el evento.");
          submitBtn.disabled = false;
          submitBtn.textContent = "✨ Crear Evento y Generar QR";
        }
      } catch (err) {
        alert("Error de conexión al crear el evento.");
        submitBtn.disabled = false;
        submitBtn.textContent = "✨ Crear Evento y Generar QR";
      }
    });
  }

  // Cargar eventos recientes
  if (recentEventsContainer) {
    fetch("/api/events")
      .then(res => res.json())
      .then(data => {
        if (data.events && data.events.length > 0) {
          recentEventsContainer.innerHTML = data.events.map(ev => `
            <a href="/e/${ev.code}" class="card" style="display:block; padding:16px; margin-bottom:12px;">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <h3 style="font-size:1.1rem; color:#fff; margin-bottom:4px;">${escapeHtml(ev.title)}</h3>
                  <div style="color:#94a3b8; font-size:0.85rem;">
                    📅 ${ev.event_date || 'Sin fecha'} &nbsp;•&nbsp; 📸 ${ev.media_count} recuerdos
                  </div>
                </div>
                <span style="color:var(--primary); font-weight:700;">Entrar →</span>
              </div>
            </a>
          `).join("");
        } else {
          recentEventsContainer.innerHTML = `<p style="color:#94a3b8; text-align:center;">Aún no hay eventos creados. ¡Crea el primero arriba!</p>`;
        }
      })
      .catch(() => {
        recentEventsContainer.innerHTML = `<p style="color:#94a3b8; text-align:center;">No se pudieron cargar eventos recientes.</p>`;
      });
  }
}

// Copiar URL al portapapeles
function copyEventUrl() {
  const input = document.getElementById("createdEventUrlInput");
  if (input) {
    input.select();
    navigator.clipboard.writeText(input.value);
    alert("¡Enlace copiado al portapapeles!");
  }
}

// ==========================================
// PÁGINA DEL EVENTO (GUEST & FEED)
// ==========================================

let currentEventCode = "";
let currentMediaList = [];
let currentFilter = "all";
let lightboxIndex = -1;

function getEventCodeFromUrl() {
  const parts = window.location.pathname.split("/");
  return parts[2] || "";
}

async function initEventPage() {
  currentEventCode = getEventCodeFromUrl();
  if (!currentEventCode) {
    alert("Código de evento no especificado.");
    window.location.href = "/";
    return;
  }

  updateGuestBadge();

  // Escuchar inputs de archivos
  const cameraInput = document.getElementById("cameraInput");
  const galleryInput = document.getElementById("galleryInput");

  if (cameraInput) {
    cameraInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
  }

  if (galleryInput) {
    galleryInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
  }

  // Cargar datos del evento y fotos
  await loadEventData();

  // Sondeo periódico para mostrar fotos en vivo cada 8 segundos
  setInterval(loadEventDataSilently, 8000);
}

async function loadEventData() {
  try {
    const res = await fetch(`/api/events/${currentEventCode}`);
    if (!res.ok) {
      document.body.innerHTML = `<div class="container" style="text-align:center; padding:50px 20px;">
        <h2>Evento no encontrado</h2>
        <p style="color:#94a3b8; margin:16px 0;">El enlace o código QR no corresponde a un evento activo.</p>
        <a href="/" class="btn btn-primary">Volver al Inicio</a>
      </div>`;
      return;
    }
    const data = await res.json();
    renderEventHeader(data.event);
    currentMediaList = data.media || [];
    renderGallery();
  } catch (err) {
    console.error("Error al cargar evento:", err);
  }
}

async function loadEventDataSilently() {
  try {
    const res = await fetch(`/api/events/${currentEventCode}`);
    if (res.ok) {
      const data = await res.json();
      // Solo actualizar si hay cambios en la cantidad de medios o likes
      if (JSON.stringify(data.media) !== JSON.stringify(currentMediaList)) {
        currentMediaList = data.media || [];
        renderGallery();
      }
    }
  } catch (err) {
    // Silencioso
  }
}

function renderEventHeader(event) {
  const titleEl = document.getElementById("eventTitleText");
  const dateEl = document.getElementById("eventDateText");
  const locEl = document.getElementById("eventLocationText");
  const descEl = document.getElementById("eventDescriptionText");
  const posterLink = document.getElementById("linkPoster");
  const slideshowLink = document.getElementById("linkSlideshow");

  if (titleEl) titleEl.textContent = event.title;
  if (dateEl) dateEl.innerHTML = event.event_date ? `📅 ${escapeHtml(event.event_date)}` : '';
  if (locEl) locEl.innerHTML = event.location ? `📍 ${escapeHtml(event.location)}` : '';
  if (descEl && event.description) {
    descEl.textContent = event.description;
    descEl.style.display = "block";
  }

  if (posterLink) posterLink.href = `/e/${event.code}/poster`;
  if (slideshowLink) slideshowLink.href = `/e/${event.code}/slideshow`;
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

  if (!container) return;

  let filtered = currentMediaList;
  if (currentFilter === "image") {
    filtered = currentMediaList.filter(m => m.file_type === "image");
  } else if (currentFilter === "video") {
    filtered = currentMediaList.filter(m => m.file_type === "video");
  }

  if (counterEl) {
    counterEl.textContent = `${currentMediaList.length} recuerdos compartidos`;
  }

  if (filtered.length === 0) {
    container.innerHTML = "";
    if (emptyState) emptyState.style.display = "block";
    return;
  }

  if (emptyState) emptyState.style.display = "none";

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

// Manejar likes
async function handleLike(mediaId) {
  try {
    const res = await fetch(`/api/media/${mediaId}/like`, { method: "POST" });
    if (res.ok) {
      const data = await res.json();
      const item = currentMediaList.find(m => m.id === mediaId);
      if (item) {
        item.likes_count = data.likes_count;
        renderGallery();
      }
    }
  } catch (err) {
    console.error("Error al dar like:", err);
  }
}

// Subida de archivos con barra de progreso
async function handleFilesSelected(fileList) {
  if (!fileList || fileList.length === 0) return;

  // Asegurar que el invitado tenga un nombre
  let uploader = getGuestName();
  if (!uploader) {
    uploader = promptGuestName(true);
  }

  const progressCard = document.getElementById("uploadProgressCard");
  const progressBar = document.getElementById("uploadProgressBar");
  const progressPercent = document.getElementById("uploadProgressPercent");
  const progressStatus = document.getElementById("uploadProgressStatus");

  if (progressCard) progressCard.style.display = "block";

  const totalFiles = fileList.length;
  let completedFiles = 0;

  for (let i = 0; i < totalFiles; i++) {
    const file = fileList[i];
    if (progressStatus) {
      progressStatus.textContent = `Subiendo recuerdo ${i + 1} de ${totalFiles}... (${file.name})`;
    }

    try {
      await uploadSingleFile(file, uploader, (percent) => {
        // Progreso global ponderado
        const overall = Math.round(((completedFiles + (percent / 100)) / totalFiles) * 100);
        if (progressBar) progressBar.style.width = `${overall}%`;
        if (progressPercent) progressPercent.textContent = `${overall}%`;
      });
      completedFiles++;
    } catch (err) {
      alert(`Error al subir ${file.name}: ${err.message || 'Error del servidor'}`);
    }
  }

  if (progressStatus) progressStatus.textContent = "¡Todos los recuerdos se subieron con éxito! 🎉";
  setTimeout(() => {
    if (progressCard) progressCard.style.display = "none";
    if (progressBar) progressBar.style.width = "0%";
  }, 2500);

  // Limpiar inputs
  const cameraInput = document.getElementById("cameraInput");
  const galleryInput = document.getElementById("galleryInput");
  if (cameraInput) cameraInput.value = "";
  if (galleryInput) galleryInput.value = "";

  // Recargar galería
  await loadEventData();
}

function uploadSingleFile(file, uploader, onProgress) {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("uploader_name", uploader);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", `/api/events/${currentEventCode}/upload`, true);

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        onProgress(percent);
      }
    };

    xhr.onload = () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        resolve(JSON.parse(xhr.responseText));
      } else {
        try {
          const errRes = JSON.parse(xhr.responseText);
          reject(new Error(errRes.detail || "Error al subir archivo"));
        } catch (_) {
          reject(new Error("Error al comunicarse con el servidor."));
        }
      }
    };

    xhr.onerror = () => reject(new Error("Error de conexión"));
    xhr.send(formData);
  });
}

// ==========================================
// VISOR LIGHTBOX (PANTALLA COMPLETA)
// ==========================================

function openLightbox(index) {
  let filtered = currentMediaList;
  if (currentFilter === "image") {
    filtered = currentMediaList.filter(m => m.file_type === "image");
  } else if (currentFilter === "video") {
    filtered = currentMediaList.filter(m => m.file_type === "video");
  }

  if (index < 0 || index >= filtered.length) return;
  lightboxIndex = index;
  const media = filtered[lightboxIndex];

  const modal = document.getElementById("lightboxModal");
  const mediaContainer = document.getElementById("lightboxMediaContainer");
  const uploaderSpan = document.getElementById("lightboxUploader");
  const downloadLink = document.getElementById("lightboxDownloadBtn");

  if (!modal || !mediaContainer) return;

  if (media.file_type === "video") {
    mediaContainer.innerHTML = `
      <video class="lightbox-media" controls autoplay playsinline style="max-height:75vh; width:100%;">
        <source src="${media.file_url}" type="video/mp4">
        Tu navegador no soporta reproducción de video.
      </video>
    `;
  } else {
    mediaContainer.innerHTML = `<img src="${media.file_url}" class="lightbox-media" alt="Foto ampliada">`;
  }

  if (uploaderSpan) {
    uploaderSpan.textContent = `Foto de: ${media.uploader_name} (${formatTimeAgo(media.created_at)})`;
  }

  if (downloadLink) {
    downloadLink.href = media.file_url;
    downloadLink.download = media.original_filename || "recuerdo";
  }

  modal.classList.add("active");
}

function closeLightbox() {
  const modal = document.getElementById("lightboxModal");
  const mediaContainer = document.getElementById("lightboxMediaContainer");
  if (modal) modal.classList.remove("active");
  if (mediaContainer) mediaContainer.innerHTML = ""; // Detener reproducción de videos
}

function nextLightbox() {
  let filtered = currentMediaList;
  if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
  if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

  if (lightboxIndex < filtered.length - 1) {
    openLightbox(lightboxIndex + 1);
  } else {
    openLightbox(0); // Volver al inicio
  }
}

function prevLightbox() {
  let filtered = currentMediaList;
  if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
  if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

  if (lightboxIndex > 0) {
    openLightbox(lightboxIndex - 1);
  } else {
    openLightbox(filtered.length - 1);
  }
}

// Descarga ZIP completa para el Anfitrión
async function downloadAllZip() {
  const pin = prompt("Ingresa el PIN de anfitrión para descargar todo el álbum en ZIP:");
  if (pin === null) return;

  const url = `/api/events/${currentEventCode}/download-zip?pin=${encodeURIComponent(pin)}`;
  window.open(url, "_blank");
}

// Borrar foto (Moderación con PIN)
async function deleteCurrentLightboxMedia() {
  let filtered = currentMediaList;
  if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
  if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

  const media = filtered[lightboxIndex];
  if (!media) return;

  const pin = prompt("Esta acción eliminará la foto permanentemente.\nIngresa el PIN de anfitrión para confirmar:");
  if (!pin) return;

  try {
    const res = await fetch(`/api/media/${media.id}?pin=${encodeURIComponent(pin)}`, {
      method: "DELETE"
    });
    const data = await res.json();
    if (res.ok) {
      alert("Recuerdo eliminado.");
      closeLightbox();
      await loadEventData();
    } else {
      alert(data.detail || "Error al eliminar.");
    }
  } catch (err) {
    alert("Error de conexión.");
  }
}

// ==========================================
// UTILIDADES
// ==========================================

function formatTimeAgo(isoString) {
  if (!isoString) return "";
  const date = new Date(isoString);
  const now = new Date();
  const diffSec = Math.floor((now - date) / 1000);

  if (diffSec < 60) return "hace unos segundos";
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `hace ${diffMin} min`;
  const diffHours = Math.floor(diffMin / 60);
  if (diffHours < 24) return `hace ${diffHours} h`;
  const diffDays = Math.floor(diffHours / 24);
  return `hace ${diffDays} d`;
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.innerText = text;
  return div.innerHTML;
}
