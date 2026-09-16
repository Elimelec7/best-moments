<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cartel Imprimible QR - Moments Drive</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
      background: #f1f5f9;
      color: #0f172a;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 24px;
      min-height: 100vh;
    }
    .no-print-bar { margin-bottom: 20px; display: flex; gap: 12px; }
    .btn-print {
      background: #f43f5e;
      color: #fff;
      font-weight: 700;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
    }
    .btn-back {
      background: #334155;
      color: #fff;
      font-weight: 600;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      font-size: 1rem;
    }
    .printable-sheet {
      width: 100%;
      max-width: 650px;
      background: #ffffff;
      border-radius: 24px;
      padding: 48px 36px;
      text-align: center;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
      position: relative;
    }
    .header-icon { font-size: 3rem; margin-bottom: 12px; display: block; }
    .celebration-title {
      font-size: 2.3rem;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 8px;
      line-height: 1.2;
    }
    .celebration-subtitle { font-size: 1.15rem; color: #64748b; margin-bottom: 28px; }
    .qr-frame {
      display: inline-block;
      padding: 18px;
      background: #ffffff;
      border: 3px dashed #cbd5e1;
      border-radius: 24px;
      margin-bottom: 28px;
    }
    .qr-frame img { width: 260px; height: 260px; display: block; }
    .steps-container {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
      margin-top: 10px;
      text-align: center;
      border-top: 1px solid #e2e8f0;
      padding-top: 24px;
    }
    .step-box {
      background: #f8fafc;
      padding: 14px 10px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
    }
    .step-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      background: #f43f5e;
      color: #fff;
      font-weight: 800;
      border-radius: 50%;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }
    .step-text {
      font-size: 0.85rem;
      color: #334155;
      font-weight: 600;
      line-height: 1.3;
    }
    .footer-note {
      margin-top: 26px;
      font-size: 0.85rem;
      color: #94a3b8;
    }
    @media print {
      body { background: #fff; padding: 0; }
      .no-print-bar { display: none !important; }
      .printable-sheet {
        box-shadow: none;
        border: none;
        max-width: 100%;
        padding: 20px;
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
    <span class="header-icon">📸 ✨</span>
    <h1 class="celebration-title" id="eventTitle">Cargando evento...</h1>
    <p class="celebration-subtitle" id="eventSub">¡Comparte tus fotos y videos con nosotros!</p>

    <div class="qr-frame">
      <img id="qrImage" src="" alt="Código QR del Evento">
    </div>

    <h3 style="font-size: 1.15rem; color: #0f172a; margin-bottom: 12px;">
      ¿Cómo compartir tus fotos?
    </h3>

    <div class="steps-container">
      <div class="step-box">
        <div class="step-number">1</div>
        <div class="step-text">Abre la cámara de tu celular y enfoca el código QR</div>
      </div>
      <div class="step-box">
        <div class="step-number">2</div>
        <div class="step-text">No necesitas descargar nada, el álbum se abre solo</div>
      </div>
      <div class="step-box">
        <div class="step-number">3</div>
        <div class="step-text">¡Toma fotos y videos o súbelos desde tu galería!</div>
      </div>
    </div>

    <p class="footer-note">
      Los recuerdos se proyectarán en las pantallas y quedarán guardados en el álbum oficial del evento ❤️
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
