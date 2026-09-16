<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Moments Drive - Álbum de Fotos y Recuerdos para Eventos</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="container">
    <!-- Header -->
    <header class="header">
      <div class="logo">
        <span class="logo-icon">📸</span>
        <span>Moments Drive</span>
      </div>
      <div>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Servidor Local (localhost:60)</span>
      </div>
    </header>

    <!-- Hero Introductorio -->
    <section class="card" style="text-align: center; padding: 32px 20px; background: radial-gradient(circle at top, rgba(244, 63, 94, 0.15) 0%, rgba(30, 41, 59, 1) 100%);">
      <h1 style="font-size: 2.1rem; font-weight: 800; margin-bottom: 12px; line-height: 1.2;">
        Captura los mejores momentos de tu fiesta en un solo lugar
      </h1>
      <p style="color: #cbd5e1; max-width: 600px; margin: 0 auto 20px; font-size: 1.05rem;">
        Crea tu evento, obtén tu <strong>código QR</strong> y colócalo en las mesas. Tus invitados escanearán y subirán fotos y videos en tiempo real sin instalar ninguna app.
      </p>
      <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; font-size: 0.9rem; color: #94a3b8;">
        <span>✨ Sin instalar aplicaciones</span>
        <span>⚡ Sube directo desde la cámara</span>
        <span>📺 Modo proyector en vivo</span>
      </div>
    </section>

    <!-- Formulario para Crear Evento -->
    <div class="card" id="createEventCard">
      <h2 class="card-title">🎉 Crear Nuevo Evento</h2>
      <p class="card-subtitle">Personaliza tu fiesta y generaremos el código QR exclusivo para tus invitados.</p>
      
      <form id="createEventForm">
        <div class="form-group">
          <label class="form-label" for="eventTitle">Nombre o Motivo de la Celebración *</label>
          <input type="text" id="eventTitle" class="form-input" placeholder="Ej: Boda de Sofía & Carlos, Mis 15 Años, Cumpleaños #30..." required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <div class="form-group">
            <label class="form-label" for="eventDate">Fecha del Evento</label>
            <input type="text" id="eventDate" class="form-input" placeholder="Ej: 25 de Octubre, 2026">
          </div>
          <div class="form-group">
            <label class="form-label" for="eventLocation">Lugar o Salón</label>
            <input type="text" id="eventLocation" class="form-input" placeholder="Ej: Salón Bellavista">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="eventDescription">Mensaje de Bienvenida para tus Invitados (Opcional)</label>
          <textarea id="eventDescription" class="form-textarea" rows="2" placeholder="¡Bienvenidos a nuestra boda! Tomen fotos de cada momento y compartan sus mejores recuerdos con nosotros ❤️"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <div class="form-group">
            <label class="form-label" for="eventTheme">Estilo de Celebración</label>
            <select id="eventTheme" class="form-select">
              <option value="celebration">Fiesta / Celebración 🎉</option>
              <option value="wedding">Boda Romántica 💍</option>
              <option value="birthday">Cumpleaños 🎂</option>
              <option value="elegant">Gala Elegante ✨</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="eventPin">PIN de Anfitrión (Para borrar o descargar)</label>
            <input type="text" id="eventPin" class="form-input" value="1234" maxlength="8" placeholder="Ej: 1234">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="eventCustomCode">Código o Enlace personalizado (Opcional)</label>
          <input type="text" id="eventCustomCode" class="form-input" placeholder="Ej: boda-sofia-carlos (dejar en blanco para generar automático)">
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
          ✨ Crear Evento y Generar Código QR
        </button>
      </form>
    </div>

    <!-- Resultado: Evento Creado y Código QR -->
    <div class="card" id="eventResultCard" style="display: none; text-align: center; border-color: var(--primary);">
      <div style="font-size: 2.5rem; margin-bottom: 8px;">🎉</div>
      <h2 style="color: #fff; font-size: 1.6rem; margin-bottom: 6px;">¡Tu Evento ha sido Creado!</h2>
      <p id="createdEventTitle" style="color: var(--primary); font-size: 1.2rem; font-weight: 700; margin-bottom: 20px;"></p>

      <div style="background: #fff; display: inline-block; padding: 14px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.5);">
        <img id="createdEventQrImg" src="" alt="Código QR del Evento" style="width: 220px; height: 220px; display: block;">
      </div>

      <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 8px;">Enlace directo para tus invitados:</p>
      <div style="display: flex; gap: 8px; max-width: 480px; margin: 0 auto 24px;">
        <input type="text" id="createdEventUrlInput" class="form-input" readonly style="text-align: center; font-weight: 600;">
        <button class="btn btn-secondary" onclick="copyEventUrl()">Copiar</button>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; max-width: 600px; margin: 0 auto;">
        <a id="btnOpenEvent" href="#" class="btn btn-primary">
          📱 Abrir Álbum en Vivo
        </a>
        <a id="btnPrintPoster" href="#" target="_blank" class="btn btn-secondary">
          🖨️ Ver Cartel para Mesas
        </a>
        <a id="btnOpenSlideshow" href="#" target="_blank" class="btn btn-outline">
          📺 Modo Proyector / TV
        </a>
      </div>
    </div>

    <!-- Eventos Recientes -->
    <div class="card">
      <h2 class="card-title" style="font-size: 1.2rem;">📂 Eventos Creados en este Servidor</h2>
      <div id="recentEventsList" style="margin-top: 14px;">
        <p style="color: #94a3b8; text-align: center;">Cargando eventos...</p>
      </div>
    </div>
  </div>

  <script>
    // Lógica para Crear Evento
    const form = document.getElementById("createEventForm");
    const resultCard = document.getElementById("eventResultCard");
    const recentEventsContainer = document.getElementById("recentEventsList");

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
        const res = await fetch("api.php?action=create_event", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.ok && data.success) {
          form.style.display = "none";
          resultCard.style.display = "block";
          
          document.getElementById("createdEventTitle").textContent = data.event.title;
          document.getElementById("createdEventQrImg").src = data.qr_url;
          document.getElementById("createdEventUrlInput").value = data.target_url;
          
          document.getElementById("btnOpenEvent").href = `event.php?code=${data.event.code}`;
          document.getElementById("btnPrintPoster").href = `poster.php?code=${data.event.code}`;
          document.getElementById("btnOpenSlideshow").href = `slideshow.php?code=${data.event.code}`;

          resultCard.scrollIntoView({ behavior: "smooth" });
          loadRecentEvents();
        } else {
          alert(data.error || "Error al crear el evento.");
          submitBtn.disabled = false;
          submitBtn.textContent = "✨ Crear Evento y Generar Código QR";
        }
      } catch (err) {
        alert("Error de conexión al crear el evento.");
        submitBtn.disabled = false;
        submitBtn.textContent = "✨ Crear Evento y Generar Código QR";
      }
    });

    function copyEventUrl() {
      const input = document.getElementById("createdEventUrlInput");
      input.select();
      navigator.clipboard.writeText(input.value);
      alert("¡Enlace copiado al portapapeles!");
    }

    function loadRecentEvents() {
      fetch("api.php?action=get_events")
        .then(res => res.json())
        .then(data => {
          if (data.events && data.events.length > 0) {
            recentEventsContainer.innerHTML = data.events.map(ev => `
              <a href="event.php?code=${ev.code}" class="card" style="display:block; padding:16px; margin-bottom:12px;">
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

    function escapeHtml(text) {
      if (!text) return "";
      const div = document.createElement("div");
      div.innerText = text;
      return div.innerHTML;
    }

    loadRecentEvents();
  </script>
</body>
</html>
