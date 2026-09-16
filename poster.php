<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cartel Imprimible QR - Experience your best moments with us!</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
      background: #090d16;
      color: #0f172a;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 28px 16px;
      min-height: 100vh;
    }
    .no-print-bar {
      margin-bottom: 22px;
      display: flex;
      gap: 12px;
    }
    .btn-print {
      background: linear-gradient(135deg, #ff3366, #d946ef);
      color: #fff;
      font-weight: 800;
      padding: 13px 26px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 1.05rem;
      box-shadow: 0 6px 18px rgba(255, 51, 102, 0.4);
      transition: transform 0.2s ease;
    }
    .btn-print:hover { transform: translateY(-2px); }
    .btn-back {
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      font-weight: 700;
      padding: 13px 22px;
      border-radius: 12px;
      text-decoration: none;
      font-size: 1rem;
    }
    /* Tarjeta Imprimible de Alta Calidad */
    .printable-sheet {
      width: 100%;
      max-width: 680px;
      background: #ffffff;
      border-radius: 28px;
      padding: 50px 40px;
      text-align: center;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
      border: 2px solid #fed7aa;
      position: relative;
    }
    .brand-tag {
      display: inline-block;
      font-size: 0.82rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #d946ef;
      background: #fdf2f8;
      padding: 6px 16px;
      border-radius: 20px;
      margin-bottom: 16px;
      border: 1px solid #fbcfe8;
    }
    .celebration-title {
      font-size: 2.5rem;
      font-weight: 900;
      color: #0f172a;
      margin-bottom: 8px;
      line-height: 1.18;
      letter-spacing: -0.02em;
    }
    .celebration-subtitle {
      font-size: 1.2rem;
      color: #64748b;
      margin-bottom: 30px;
      font-weight: 600;
    }
    .qr-frame {
      display: inline-block;
      padding: 22px;
      background: #ffffff;
      border: 3px dashed #f43f5e;
      border-radius: 26px;
      margin-bottom: 30px;
      box-shadow: 0 8px 24px rgba(244, 63, 94, 0.15);
    }
    .qr-frame img {
      width: 270px;
      height: 270px;
      display: block;
      border-radius: 8px;
    }
    /* Pasos Gráficos */
    .steps-container {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
      margin-top: 10px;
      text-align: center;
      border-top: 2px solid #f1f5f9;
      padding-top: 26px;
    }
    .step-box {
      background: #f8fafc;
      padding: 16px 12px;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
    }
    .step-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, #ff3366, #d946ef);
      color: #fff;
      font-weight: 900;
      border-radius: 50%;
      font-size: 0.95rem;
      margin-bottom: 10px;
      box-shadow: 0 4px 10px rgba(255, 51, 102, 0.3);
    }
    .step-text {
      font-size: 0.9rem;
      color: #1e293b;
      font-weight: 700;
      line-height: 1.35;
    }
    .footer-note {
      margin-top: 30px;
      font-size: 0.92rem;
      color: #64748b;
      font-weight: 600;
    }
    @media print {
      body {
        background: #fff;
        padding: 0;
      }
      .no-print-bar {
        display: none !important;
      }
      .printable-sheet {
        box-shadow: none;
        border: none;
        max-width: 100%;
        padding: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="no-print-bar">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Cartel / Guardar PDF</button>
    <a id="btnBackToEvent" href="#" class="btn-back">← Volver al Álbum</a>
  </div>

  <div class="printable-sheet">
    <span class="brand-tag">✨ Experience your best moments with us! ✨</span>
    <h1 class="celebration-title" id="eventTitle">Cargando evento...</h1>
    <p class="celebration-subtitle" id="eventSub">¡Comparte tus fotos y videos en tiempo real con nosotros!</p>

    <div class="qr-frame">
      <img id="qrImage" src="" alt="Código QR del Evento">
    </div>

    <h3 style="font-size: 1.25rem; color: #0f172a; font-weight: 800; margin-bottom: 14px;">
      ¿Cómo compartir tus recuerdos?
    </h3>

    <div class="steps-container">
      <div class="step-box">
        <div class="step-number">1</div>
        <div class="step-text">Abre la cámara de tu celular y enfoca el código QR</div>
      </div>
      <div class="step-box">
        <div class="step-number">2</div>
        <div class="step-text">No necesitas descargar apps, la web se abre sola</div>
      </div>
      <div class="step-box">
        <div class="step-number">3</div>
        <div class="step-text">¡Toma fotos o videos y saldrán en vivo en la fiesta!</div>
      </div>
    </div>

    <p class="footer-note">
      Los recuerdos se proyectarán en las pantallas y quedarán guardados en el álbum oficial ❤️
    </p>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const eventCode = urlParams.get('code') || '';
    document.getElementById("btnBackToEvent").href = `event.php?code=${encodeURIComponent(eventCode)}`;

    fetch(`api.php?action=get_event&code=${encodeURIComponent(eventCode)}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById("eventTitle").textContent = data.event.title;
        if (data.event.event_date) {
          document.getElementById("eventSub").textContent = `Celebración - ${data.event.event_date}`;
        }
        document.getElementById("qrImage").src = data.qr_url;
      })
      .catch(err => {
        document.getElementById("eventTitle").textContent = "Error al cargar evento";
      });
  </script>
</body>
</html>
