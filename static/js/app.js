/**
 * Experience your best moments with us! - Lógica Frontend
 */

// Utilidad para obtener y guardar el nombre del invitado
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

// Utilidades para PIN de evento
function getEventPin(code) {
  const c = code || currentEventCode;
  return localStorage.getItem("moments_pin_" + c) || "";
}

function setEventPin(pin, code) {
  const c = code || currentEventCode;
  if (pin) localStorage.setItem("moments_pin_" + c, pin.trim());
}

// ==========================================
// PÁGINA PRINCIPAL: CREAR EVENTO Y SEGURIDAD
// ==========================================

let selectedEventCode = "";

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

      const adminPin = document.getElementById("eventPin").value || "1234";

      const payload = {
        title: document.getElementById("eventTitle").value,
        event_date: document.getElementById("eventDate").value,
        location: document.getElementById("eventLocation").value,
        description: document.getElementById("eventDescription").value,
        admin_pin: adminPin,
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
          
          setEventPin(adminPin, data.event.code);
          localStorage.setItem("moments_host_" + data.event.code, "true");

          document.getElementById("createdEventTitle").textContent = data.event.title;
          document.getElementById("createdEventQrImg").src = data.qr_url;
          document.getElementById("createdEventUrlInput").value = data.target_url;
          
          document.getElementById("btnOpenEvent").href = `/e/${data.event.code}?pin=${encodeURIComponent(adminPin)}`;
          document.getElementById("btnPrintPoster").href = `/e/${data.event.code}/poster?pin=${encodeURIComponent(adminPin)}`;
          document.getElementById("btnOpenSlideshow").href = `/e/${data.event.code}/slideshow?pin=${encodeURIComponent(adminPin)}`;

          resultCard.scrollIntoView({ behavior: "smooth" });
          loadRecentEvents();
        } else {
          alert(data.detail || "Error al crear el evento.");
          submitBtn.disabled = false;
          submitBtn.textContent = "🚀 ¡Crear Evento y Obtener Código QR!";
        }
      } catch (err) {
        alert("Error de conexión al crear el evento.");
        submitBtn.disabled = false;
        submitBtn.textContent = "🚀 ¡Crear Evento y Obtener Código QR!";
      }
    });
  }

  loadRecentEvents();
}

function loadRecentEvents() {
  const container = document.getElementById("recentEventsList");
  if (!container) return;

  fetch("/api/events")
    .then(res => res.json())
    .then(data => {
      if (data.events && data.events.length > 0) {
        container.innerHTML = data.events.map(ev => `
          <div onclick="openProtectedEvent('${ev.code}', '${escapeHtml(ev.title)}')" class="glass-card" style="display:block; padding:18px; margin-bottom:12px; cursor:pointer;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div>
                <h3 style="font-size:1.15rem; color:#fff; font-weight:800; margin-bottom:4px;">
                  🔒 ${escapeHtml(ev.title)}
                </h3>
                <div style="color:#94a3b8; font-size:0.86rem; display:flex; gap:12px; align-items:center;">
                  <span>📅 ${ev.event_date || 'Sin fecha'}</span>
                  <span>📸 <strong>${ev.media_count}</strong> recuerdos</span>
                </div>
              </div>
              <span style="color:var(--primary); font-weight:800; font-size:0.95rem; background:rgba(255,51,102,0.12); padding:6px 14px; border-radius:12px; border:1px solid rgba(255,51,102,0.3);">
                Ingresar con PIN →
              </span>
            </div>
          </div>
        `).join("");
      } else {
        container.innerHTML = `<p style="color:#94a3b8; text-align:center; padding:20px;">Aún no hay eventos creados. ¡Crea el primero arriba!</p>`;
      }
    })
    .catch(() => {
      container.innerHTML = `<p style="color:#94a3b8; text-align:center;">No se pudieron cargar los eventos.</p>`;
    });
}

function openProtectedEvent(code, title) {
  const savedPin = getEventPin(code);
  if (savedPin) {
    window.location.href = `/e/${encodeURIComponent(code)}?pin=${encodeURIComponent(savedPin)}`;
    return;
  }
  selectedEventCode = code;
  const overlay = document.getElementById("pinModalOverlay");
  if (overlay) {
    document.getElementById("modalEventTitle").textContent = title;
    document.getElementById("modalPinInput").value = "";
    document.getElementById("modalPinError").style.display = "none";
    overlay.classList.add("active");
    setTimeout(() => document.getElementById("modalPinInput").focus(), 100);
  } else {
    // Si no hay modal, ir directo y la página del evento mostrará el pin gate
    window.location.href = `/e/${encodeURIComponent(code)}`;
  }
}

function closePinModal() {
  const overlay = document.getElementById("pinModalOverlay");
  if (overlay) overlay.classList.remove("active");
}

async function submitModalPin(e) {
  e.preventDefault();
  const pin = document.getElementById("modalPinInput").value.trim();
  const btn = document.getElementById("btnModalSubmit");
  btn.disabled = true;
  btn.textContent = "Verificando...";

  try {
    const res = await fetch(`/api/events/${encodeURIComponent(selectedEventCode)}/verify-pin`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ pin: pin })
    });
    const data = await res.json();
    btn.disabled = false;
    btn.textContent = "Entrar";

    if (data.valid) {
      setEventPin(pin, selectedEventCode);
      window.location.href = `/e/${encodeURIComponent(selectedEventCode)}?pin=${encodeURIComponent(pin)}`;
    } else {
      document.getElementById("modalPinError").style.display = "block";
      document.getElementById("modalPinInput").select();
    }
  } catch (err) {
    btn.disabled = false;
    btn.textContent = "Entrar";
    alert("Error al verificar el PIN.");
  }
}

function copyEventUrl() {
  const input = document.getElementById("createdEventUrlInput");
  if (input) {
    input.select();
    navigator.clipboard.writeText(input.value);
    alert("✨ ¡Enlace copiado al portapapeles!");
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

  // Si viene con el parámetro pin en la URL (al escanear QR)
  const urlParams = new URLSearchParams(window.location.search);
  const urlPin = urlParams.get("pin");
  if (urlPin) {
    setEventPin(urlPin);
  }

  updateGuestBadge();

  // Escuchar inputs de archivos (Foto directa, Video directo, Galería)
  const photoInput = document.getElementById("cameraPhotoInput");
  const videoInput = document.getElementById("cameraVideoInput");
  const galleryInput = document.getElementById("galleryInput");

  if (photoInput) {
    photoInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
  }
  if (videoInput) {
    videoInput.addEventListener("change", (e) => handleFilesSelected(e.target.files));
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
  const pin = getEventPin();

  try {
    const res = await fetch(`/api/events/${currentEventCode}?pin=${encodeURIComponent(pin)}`);
    if (!res.ok) {
      document.body.innerHTML = `<div class="container" style="text-align:center; padding:60px 20px;">
        <h2 style="font-size:2rem; margin-bottom:12px;">Evento no encontrado</h2>
        <p style="color:#94a3b8; margin-bottom:20px;">El enlace o código QR no corresponde a un evento activo.</p>
        <a href="/" class="btn btn-primary">Volver al Inicio</a>
      </div>`;
      return;
    }
    const data = await res.json();

    const gate = document.getElementById("pinGateCard");
    const main = document.getElementById("eventMainContent");
    const btm = document.getElementById("mobileBottomBar");

    // Si el evento está bloqueado por PIN
    if (data.locked) {
      if (gate) gate.style.display = "block";
      if (main) main.style.display = "none";
      if (btm) btm.style.display = "none";
      const gateTitle = document.getElementById("pinGateTitle");
      if (gateTitle && data.event) gateTitle.textContent = data.event.title;
      return;
    }

    // Desbloqueado
    if (gate) gate.style.display = "none";
    if (main) main.style.display = "block";
    if (btm) btm.style.display = "flex";

    renderEventHeader(data.event);
    currentMediaList = data.media || [];
    renderGallery();
  } catch (err) {
    console.error("Error al cargar evento:", err);
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
    const res = await fetch(`/api/events/${currentEventCode}?pin=${encodeURIComponent(inputPin)}`);
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
      const btm = document.getElementById("mobileBottomBar");
      if (btm) btm.style.display = "flex";

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

async function loadEventDataSilently() {
  const pin = getEventPin();
  if (!pin) return; // Si no hay PIN, no sondear

  try {
    const res = await fetch(`/api/events/${currentEventCode}?pin=${encodeURIComponent(pin)}`);
    if (res.ok) {
      const data = await res.json();
      if (!data.locked && JSON.stringify(data.media) !== JSON.stringify(currentMediaList)) {
        currentMediaList = data.media || [];
        renderGallery();
      }
    }
  } catch (err) {}
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

  const titleEl = document.getElementById("eventTitleText");
  const dateEl = document.getElementById("eventDateText");
  const locEl = document.getElementById("eventLocationText");
  const countEl = document.getElementById("eventHeaderCount");
  const descWrapper = document.getElementById("eventDescriptionWrapper");
  const descEl = document.getElementById("eventDescriptionText");
  const posterLink = document.getElementById("linkPoster");
  const slideshowLink = document.getElementById("linkSlideshow");

  if (titleEl) titleEl.textContent = event.title;
  if (dateEl) dateEl.innerHTML = event.event_date ? `📅 ${escapeHtml(event.event_date)}` : '';
  if (locEl) locEl.innerHTML = event.location ? `📍 ${escapeHtml(event.location)}` : '';
  if (countEl) countEl.textContent = `📸 ${currentMediaList.length} recuerdos`;

  if (descWrapper && descEl) {
    if (event.description && event.description.trim()) {
      descEl.textContent = `“${event.description}”`;
      descWrapper.style.display = "block";
    } else {
      descWrapper.style.display = "none";
    }
  }

  const pinParam = encodeURIComponent(getEventPin());
  if (posterLink) posterLink.href = `/e/${event.code}/poster?pin=${pinParam}`;
  if (slideshowLink) slideshowLink.href = `/e/${event.code}/slideshow?pin=${pinParam}`;

  // Control de visibilidad del botón Cartel QR: Solo visible para el anfitrión creador
  updateHostVisibility();
}

function updateHostVisibility() {
  const isHost = localStorage.getItem("moments_host_" + currentEventCode) === "true";
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

  fetch(`/api/events/${encodeURIComponent(currentEventCode)}/verify-pin`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ pin: entered.trim() })
  })
    .then(r => r.json())
    .then(data => {
      if (data.valid) {
        localStorage.setItem("moments_host_" + currentEventCode, "true");
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

// Subida de archivos con barra de progreso y PIN de seguridad
async function handleFilesSelected(fileList) {
  if (!fileList || fileList.length === 0) return;

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
      progressStatus.textContent = `Subiendo momento ${i + 1} de ${totalFiles}... (${file.name})`;
    }

    try {
      await uploadSingleFile(file, uploader, (percent) => {
        const overall = Math.round(((completedFiles + (percent / 100)) / totalFiles) * 100);
        if (progressBar) progressBar.style.width = `${overall}%`;
        if (progressPercent) progressPercent.textContent = `${overall}%`;
      });
      completedFiles++;
    } catch (err) {
      alert(`Error al subir ${file.name}: ${err.message || 'Error del servidor'}`);
    }
  }

  if (progressStatus) progressStatus.textContent = "¡Momento compartido con éxito! 🎉✨";
  setTimeout(() => {
    if (progressCard) progressCard.style.display = "none";
    if (progressBar) progressBar.style.width = "0%";
  }, 2200);

  const photoInput = document.getElementById("cameraPhotoInput");
  const videoInput = document.getElementById("cameraVideoInput");
  const galleryInput = document.getElementById("galleryInput");
  if (photoInput) photoInput.value = "";
  if (videoInput) videoInput.value = "";
  if (galleryInput) galleryInput.value = "";

  await loadEventData();
}

function uploadSingleFile(file, uploader, onProgress) {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("uploader_name", uploader);
    formData.append("pin", getEventPin());

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
      <video class="lightbox-media" controls autoplay playsinline style="max-height:74vh; width:100%;">
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
  if (mediaContainer) mediaContainer.innerHTML = "";
}

function nextLightbox() {
  let filtered = currentMediaList;
  if (currentFilter === "image") filtered = currentMediaList.filter(m => m.file_type === "image");
  if (currentFilter === "video") filtered = currentMediaList.filter(m => m.file_type === "video");

  if (lightboxIndex < filtered.length - 1) {
    openLightbox(lightboxIndex + 1);
  } else {
    openLightbox(0);
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
  let pin = getEventPin();
  if (!pin) {
    pin = prompt("Ingresa el PIN de anfitrión para descargar todo el álbum en ZIP:");
  }
  if (!pin) return;

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
