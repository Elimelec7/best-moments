<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Experience your best moments with us! - Álbum en Vivo para Eventos</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="container">
    <!-- Header con Nueva Marca Dinámica -->
    <header class="header">
      <div class="brand-wrapper">
        <div class="brand-icon">✨</div>
        <div class="brand-text">
          <span class="brand-title">Experience your best moments with us!</span>
          <span class="brand-slogan">Álbum en la nube interactivo con Código QR</span>
        </div>
      </div>
      <div>
        <span class="hero-pill" style="margin-bottom: 0;">🎉 En Vivo</span>
      </div>
    </header>

    <!-- Banner Hero Gráfico y Dinámico -->
    <section class="hero-banner">
      <div class="hero-pill">📸 El Drive Interactivo de tus Celebraciones</div>
      <h1 class="hero-heading">
        ¡Captura y comparte los mejores momentos de tu fiesta!
      </h1>
      <p class="hero-desc">
        Crea tu evento en segundos y obtén un <strong>Código QR exclusivo</strong> para las mesas. Tus invitados toman fotos y videos desde su celular y se proyectan al instante sin descargar ninguna app.
      </p>

      <!-- Chips de Selección Rápida de Tipo de Evento -->
      <div class="category-chips">
        <button type="button" class="chip-btn" onclick="quickFill('Boda de ', 'wedding', '¡Bienvenidos a nuestra boda! Tomen fotos de cada momento especial y compártanlas con nosotros ❤️')">
          💍 Boda Romántica
        </button>
        <button type="button" class="chip-btn" onclick="quickFill('Cumpleaños de ', 'birthday', '¡Feliz Cumpleaños! Sube tus mejores fotos y videos de la fiesta 🎂🎉')">
          🎂 Cumpleaños
        </button>
        <button type="button" class="chip-btn" onclick="quickFill('Mis 15 Años - ', 'celebration', '¡Bienvenidos a mis 15 años! Comparte tus fotos de esta noche inolvidable ✨')">
          👑 Fiesta de 15
        </button>
        <button type="button" class="chip-btn" onclick="quickFill('Graduación de ', 'celebration', '¡Lo logramos! Comparte aquí todos los recuerdos de nuestro grado 🎓')">
          🎓 Graduación
        </button>
        <button type="button" class="chip-btn" onclick="quickFill('Fiesta ', 'celebration', '¡A festejar con todo! Tomen fotos y saldrán en la pantalla grande 🥳')">
          🥂 Fiesta & Rumba
        </button>
      </div>
    </section>

    <!-- Formulario para Crear Evento con Glassmorphism -->
    <div class="glass-card" id="createEventCard">
      <h2 class="card-title">
        <span>✨</span> Crear Nuevo Evento
      </h2>
      <p class="card-subtitle">Personaliza tu celebración y generaremos el código QR dinámico listo para imprimir y proyectar.</p>
      
      <form id="createEventForm">
        <div class="form-group">
          <label class="form-label" for="eventTitle">Nombre o Motivo de la Celebración *</label>
          <input type="text" id="eventTitle" class="form-input" placeholder="Ej: Boda de Sofía & Carlos, Cumpleaños #30 de Juan..." required>
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
          <label class="form-label" for="eventDescription">Mensaje de Bienvenida para tus Invitados</label>
          <textarea id="eventDescription" class="form-textarea" rows="2" placeholder="¡Bienvenidos a nuestra celebración! Tomen fotos de cada instante y disfruten la fiesta ❤️"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <div class="form-group">
            <label class="form-label" for="eventTheme">Estilo Visual</label>
            <select id="eventTheme" class="form-select">
              <option value="celebration">Fiesta / Celebración 🎉</option>
              <option value="wedding">Boda Romántica 💍</option>
              <option value="birthday">Cumpleaños Festivo 🎂</option>
              <option value="elegant">Gala Elegante ✨</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="eventPin">PIN de Anfitrión (Para borrar o descargar)</label>
            <input type="text" id="eventPin" class="form-input" value="1234" maxlength="8" placeholder="Ej: 1234">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="eventCustomCode">Código o Enlace corto personalizado (Opcional)</label>
          <input type="text" id="eventCustomCode" class="form-input" placeholder="Ej: boda-sofia-carlos (dejar en blanco para autogenerar)">
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
          🚀 ¡Crear Evento y Obtener Código QR!
        </button>
      </form>
    </div>

    <!-- Resultado Dinámico: Evento Creado -->
    <div class="glass-card" id="eventResultCard" style="display: none; text-align: center; border-color: var(--primary);">
      <div style="font-size: 2.8rem; margin-bottom: 8px;">🎉 ✨</div>
      <h2 style="color: #fff; font-size: 1.8rem; font-weight: 900; margin-bottom: 6px;">¡Tu Álbum Está Listo!</h2>
      <p id="createdEventTitle" style="color: var(--primary); font-size: 1.3rem; font-weight: 800; margin-bottom: 22px;"></p>

      <!-- Frame Iluminado del Código QR -->
      <div style="background: #ffffff; display: inline-block; padding: 18px; border-radius: 24px; margin-bottom: 22px; box-shadow: 0 12px 35px var(--primary-glow);">
        <img id="createdEventQrImg" src="" alt="Código QR del Evento" style="width: 230px; height: 230px; display: block; border-radius: 8px;">
      </div>

      <p style="color: #cbd5e1; font-size: 0.95rem; margin-bottom: 10px;">Enlace directo para compartir por WhatsApp o redes:</p>
      <div style="display: flex; gap: 8px; max-width: 520px; margin: 0 auto 26px;">
        <input type="text" id="createdEventUrlInput" class="form-input" readonly style="text-align: center; font-weight: 700; font-size: 0.92rem;">
        <button type="button" class="btn btn-secondary" onclick="copyEventUrl()">📋 Copiar</button>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; max-width: 650px; margin: 0 auto;">
        <a id="btnOpenEvent" href="#" class="btn btn-primary">
          📱 Abrir Álbum en Vivo
        </a>
        <a id="btnPrintPoster" href="#" target="_blank" class="btn btn-secondary">
          🖨️ Cartel Imprimible Mesas
        </a>
        <a id="btnOpenSlideshow" href="#" target="_blank" class="btn btn-outline">
          📺 Modo Proyector / TV
        </a>
      </div>
    </div>

    <!-- Lista de Eventos Recientes -->
    <div class="glass-card">
      <h2 class="card-title" style="font-size: 1.25rem;">
        <span>📂</span> Eventos en este Servidor
      </h2>
      <div id="recentEventsList" style="margin-top: 16px;">
        <p style="color: #94a3b8; text-align: center;">Cargando eventos...</p>
      </div>
    </div>
  </div>

  <script>
    function quickFill(prefix, theme, desc) {
      const input = document.getElementById("eventTitle");
      if (!input.value || input.value.startsWith("Boda") || input.value.startsWith("Cumple") || input.value.startsWith("Mis 15") || input.value.startsWith("Gradua") || input.value.startsWith("Fiesta")) {
        input.value = prefix;
        input.focus();
      }
      document.getElementById("eventTheme").value = theme;
      document.getElementById("eventDescription").value = desc;
    }

    const form = document.getElementById("createEventForm");
    const resultCard = document.getElementById("eventResultCard");
    const recentEventsContainer = document.getElementById("recentEventsList");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector("button[type='submit']");
      submitBtn.disabled = true;
      submitBtn.textContent = "Generando QR y preparando álbum...";

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
          headers: { "Content-Type": "application/json; charset=utf-8" },
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
          submitBtn.textContent = "🚀 ¡Crear Evento y Obtener Código QR!";
        }
      } catch (err) {
        alert("Error de conexión con el servidor.");
        submitBtn.disabled = false;
        submitBtn.textContent = "🚀 ¡Crear Evento y Obtener Código QR!";
      }
    });

    function copyEventUrl() {
      const input = document.getElementById("createdEventUrlInput");
      input.select();
      navigator.clipboard.writeText(input.value);
      alert("✨ ¡Enlace copiado al portapapeles!");
    }
  </script>

  <!-- Modal de Seguridad PIN para acceder a un evento privado -->
  <div class="modal-overlay" id="pinModalOverlay" onclick="closePinModal()">
    <div class="glass-card" onclick="event.stopPropagation()" style="max-width: 440px; width: 100%; text-align: center; padding: 36px 24px; border-color: var(--primary);">
      <div style="font-size: 2.8rem; margin-bottom: 8px;">🔒</div>
      <h3 style="font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 6px;">Evento Privado</h3>
      <p id="modalEventTitle" style="color: var(--primary); font-weight: 700; margin-bottom: 8px;"></p>
      <p style="color: #cbd5e1; font-size: 0.9rem; margin-bottom: 20px;">
        Ingresa el PIN de anfitrión o de invitado para ver y subir fotos a esta celebración:
      </p>
      <form onsubmit="submitModalPin(event)">
        <input type="password" id="modalPinInput" class="form-input" placeholder="Ingresa el PIN" maxlength="12" style="text-align: center; font-size: 1.3rem; letter-spacing: 5px; font-weight: 800; margin-bottom: 14px;" required autofocus>
        <div style="display: flex; gap: 10px;">
          <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="closePinModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary" style="flex: 1;" id="btnModalSubmit">Entrar</button>
        </div>
      </form>
      <div id="modalPinError" style="display: none; color: #f87171; font-weight: 700; margin-top: 14px; font-size: 0.88rem;">
        ❌ PIN incorrecto para este evento.
      </div>
    </div>
  </div>

  <!-- Lista de Eventos Recientes -->
  <div class="container">
    <div class="glass-card">
      <h2 class="card-title" style="font-size: 1.25rem;">
        <span>🔒</span> Eventos en este Servidor (Protegidos con PIN)
      </h2>
      <div id="recentEventsList" style="margin-top: 16px;">
        <p style="color: #94a3b8; text-align: center;">Cargando eventos...</p>
      </div>
    </div>
  </div>

  <script>
    let selectedEventCode = "";

    function openProtectedEvent(code, title) {
      const savedPin = localStorage.getItem('moments_pin_' + code);
      if (savedPin) {
        window.location.href = `event.php?code=${encodeURIComponent(code)}&pin=${encodeURIComponent(savedPin)}`;
        return;
      }
      selectedEventCode = code;
      document.getElementById("modalEventTitle").textContent = title;
      document.getElementById("modalPinInput").value = "";
      document.getElementById("modalPinError").style.display = "none";
      document.getElementById("pinModalOverlay").classList.add("active");
      setTimeout(() => document.getElementById("modalPinInput").focus(), 100);
    }

    function closePinModal() {
      document.getElementById("pinModalOverlay").classList.remove("active");
    }

    async function submitModalPin(e) {
      e.preventDefault();
      const pin = document.getElementById("modalPinInput").value.trim();
      const btn = document.getElementById("btnModalSubmit");
      btn.disabled = true;
      btn.textContent = "Verificando...";

      try {
        const res = await fetch(`api.php?action=verify_pin&code=${encodeURIComponent(selectedEventCode)}&pin=${encodeURIComponent(pin)}`);
        const data = await res.json();
        btn.disabled = false;
        btn.textContent = "Entrar";

        if (data.valid) {
          localStorage.setItem('moments_pin_' + selectedEventCode, pin);
          window.location.href = `event.php?code=${encodeURIComponent(selectedEventCode)}&pin=${encodeURIComponent(pin)}`;
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

    function loadRecentEvents() {
      fetch("api.php?action=get_events")
        .then(res => res.json())
        .then(data => {
          if (data.events && data.events.length > 0) {
            recentEventsContainer.innerHTML = data.events.map(ev => `
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
                  <span style="color:var(--primary); font-weight:800; font-size:0.95rem; background:rgba(255,51,102,0.12); padding:6px 12px; border-radius:12px; border:1px solid rgba(255,51,102,0.3);">
                    Ingresar con PIN →
                  </span>
                </div>
              </div>
            `).join("");
          } else {
            recentEventsContainer.innerHTML = `<p style="color:#94a3b8; text-align:center; padding: 20px;">Aún no hay eventos creados. ¡Crea el primero arriba!</p>`;
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
