<?php
session_start();
// Control de acceso
$allowed_roles = ['admin', 'events', 'viewer'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
// El head.php ya suele abrir la etiqueta <html> y <head>
include_once '../assets/head.php';
?>

<style>
    /* VARIABLES Y RESET */
    :root { --aisc-pink: #EB178E; --aisc-blue: #20CCF1; }
    
    /* Contenedor principal para que el Nav y el Builder convivan */
    .main-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100%;
    }

    body {
        color: #333;
    }

    /* El área de trabajo debajo del Nav */
    .builder-container { 
        display: flex; 
        flex: 1;
        overflow: hidden; /* Evita scroll doble */

    }

    /* Editor (Izquierda) */
    #editor { 
        width: 50%; 
        padding: 20px; 
        overflow-y: auto; 
        background: #fff; 
        border-right: 2px solid #ddd;
        z-index: 10;
    }

    /* Vista Previa (Derecha) */
    #preview-pane { 
        width: 50%;
        padding: 40px; 
        overflow-y: auto; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
    }

    /* Tarjetas de los bloques */
    .chunk-card { 
        border-left: 5px solid var(--aisc-pink); 
        padding: 15px; 
        margin-bottom: 15px; 
        background: #fafafa; 
        border-radius: 4px; 
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .chunk-card input, .chunk-card textarea { 
        width: 100%; 
        margin: 5px 0; 
        padding: 8px; 
        border: 1px solid #ccc; 
        border-radius: 4px; 
    }

    .btn-pink { background-color: var(--aisc-pink) !important; color: white !important; }
    .btn-blue { background-color: var(--aisc-blue) !important; color: white !important; }

    /* El contenedor del email real */
    #email-preview-frame {
        background: #fff;
        width: 600px;
        min-height: 400px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .btn-delete { position: absolute; top: 5px; right: 5px; border: none; background: none; font-size: 20px; color: #cc0000; }
</style>

<body>

<div class="main-wrapper scroll-margin">
    <?php 
    if($_SESSION['role'] === 'admin'){
        include_once 'dashboard_nav.php';
    } else {
        include_once 'dashboard_nav_noadmin.php';
    } 
    ?>

    <div class="builder-container">
        
        <div id="editor">
            <h2 class="h4 mb-3" style="color: var(--aisc-pink); font-weight: bold;">AISC Mail-Builder</h2>
            
            <div class="controls d-grid gap-2 mb-3" style="grid-template-columns: 1fr 1fr;">
                <button class="btn btn-pink btn-sm" onclick="addChunk('header')">+ Título</button>
                <button class="btn btn-pink btn-sm" onclick="addChunk('subheader')">+ Subtítulo</button>
                <button class="btn btn-pink btn-sm" onclick="addChunk('image')">+ Imagen</button>
                <button class="btn btn-pink btn-sm" onclick="addChunk('text')">+ Texto Principal</button>
                <button class="btn btn-pink btn-sm" onclick="addChunk('event')">+ Información Evento</button>
                <button class="btn btn-blue btn-sm" onclick="addChunk('button')">+ Botón</button>
            </div>

            <div id="chunks-container"></div>

            <div class="p-3 mb-3 bg-light border rounded">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show-footer" checked onchange="updatePreview()">
                    <label class="form-check-label fw-bold" for="show-footer">Incluir Footer AISC Madrid</label>
                </div>
            </div>

            <hr>
            <h5 class="h2">Código Final</h5>
            <textarea id="code-output" class="form-control mb-2" style="height: 100px; font-size: 11px; font-family: monospace;" readonly onclick="this.select(); document.execCommand('copy');"></textarea>
            
            <button class="btn btn-blue w-100" onclick="downloadHTML()">
                <i class="bi bi-download"></i> Descargar HTML
            </button>
        </div>

        <div id="preview-pane">
            <div id="email-preview-frame">
                <div id="email-preview"></div>
            </div>
        </div>

    </div>
</div>

<script>
    let chunks = [];

    function addChunk(type) {
        const id = Date.now();
        let data = { id, type };
        
        if (type === 'header') data.val = 'Título';
        if (type === 'subheader') data.val = 'Subtítulo Sección';
        if (type === 'image') data.val = 'https://aiscmadrid.com/images/logos/SVG/AISCMadridLogoAndLetters.svg';
        if (type === 'text') { data.sub = 'Subtítulo'; data.body = 'Información extra'; data.align = 'center';}
        if (type === 'event') { data.title = 'Título Evento'; data.info = 'Extra info'; data.date = '20 de enero'; data.time = '10:00h'; data.place = 'Aula ejemplo'; }
        if (type === 'button') { data.text = 'Ver eventos'; data.url = 'https://aiscmadrid.com/#events'; }

        chunks.push(data);
        renderEditor();
        updatePreview();
    }

    function updateChunk(id, field, value) {
        const chunk = chunks.find(c => c.id === id);
        if (chunk) chunk[field] = value;
        updatePreview();
    }

    function removeChunk(id) {
        chunks = chunks.filter(c => c.id !== id);
        renderEditor();
        updatePreview();
    }

    function moveChunk(id, direction) {
        const index = chunks.findIndex(c => c.id === id);
        if (index === -1) return;
        const newIndex = direction === 'up' ? index - 1 : index + 1;
        if (newIndex < 0 || newIndex >= chunks.length) return;
        
        [chunks[index], chunks[newIndex]] = [chunks[newIndex], chunks[index]];
        renderEditor();
        updatePreview();
    }

    function insertBold(id) {
        const textarea = document.getElementById(`txt-${id}`);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selectedText = text.substring(start, end);
        const newText = text.substring(0, start) + "<b>" + selectedText + "</b>" + text.substring(end);
        
        textarea.value = newText;
        updateChunk(id, 'body', newText);
        textarea.focus();
    }

    function renderEditor() {
        const container = document.getElementById('chunks-container');
        container.innerHTML = '';

        chunks.forEach((c, index) => { 
            const card = document.createElement('div');
            card.className = 'chunk-card';

            let navButtons = `
                <div style="position: absolute; top: 5px; right: 35px; display: flex; gap: 5px;">
                    <button class="p-0 border-0 bg-transparent" onclick="moveChunk(${c.id}, 'up')" ${index === 0 ? 'disabled' : ''} style="color: ${index === 0 ? '#ccc' : 'var(--aisc-pink)'}">▲</button>
                    <button class="p-0 border-0 bg-transparent" onclick="moveChunk(${c.id}, 'down')" ${index === chunks.length - 1 ? 'disabled' : ''} style="color: ${index === chunks.length - 1 ? '#ccc' : 'var(--aisc-pink)'}">▼</button>
                </div>
            `;

            let html = `<button class="btn-delete" onclick="removeChunk(${c.id})">&times;</button>${navButtons}<small class="text-muted fw-bold">${c.type.toUpperCase()}</small>`;
            
            if (c.type === 'header' || c.type === 'image' || c.type === 'subheader') {
                html += `<input type="text" value="${c.val}" oninput="updateChunk(${c.id}, 'val', this.value)">`;
            } else if (c.type === 'text') {
                html += `<div class="mb-2">
                            <small class="text-muted">Alineación:</small>
                            <select class="form-select form-select-sm d-inline-block w-auto" onchange="updateChunk(${c.id}, 'align', this.value)">
                                <option value="center" ${c.align === 'center' ? 'selected' : ''}>Centrado</option>
                                <option value="left" ${c.align === 'left' ? 'selected' : ''}>Izquierda</option>
                                <option value="right" ${c.align === 'right' ? 'selected' : ''}>Derecha</option>
                            </select>
                        </div>`;

                html += `<input type="text" placeholder="Subtítulo" value="${c.sub || ''}" oninput="updateChunk(${c.id}, 'sub', this.value)">`;

                html += `<button type="button" class="btn btn-light btn-sm mb-1" onclick="insertBold(${c.id})"><b>B</b> Bold</button>`;
                html += `<textarea id="txt-${c.id}" oninput="updateChunk(${c.id}, 'body', this.value)">${c.body || ''}</textarea>`;
            } else if (c.type === 'event') {
                html += `<input type="text" placeholder="Título" value="${c.title || ''}" oninput="updateChunk(${c.id}, 'title', this.value)">`;
                html += `<input type="text" placeholder="Info extra" value="${c.info || ''}" oninput="updateChunk(${c.id}, 'info', this.value)">`;
                html += `<input type="text" placeholder="Fecha" value="${c.date || ''}" oninput="updateChunk(${c.id}, 'date', this.value)">`;
                html += `<input type="text" placeholder="Hora" value="${c.time || ''}" oninput="updateChunk(${c.id}, 'time', this.value)">`;
                html += `<input type="text" placeholder="Lugar" value="${c.place || ''}" oninput="updateChunk(${c.id}, 'place', this.value)">`;
            } else if (c.type === 'button') {
                html += `<input type="text" placeholder="Texto" value="${c.text || ''}" oninput="updateChunk(${c.id}, 'text', this.value)">`;
                html += `<input type="text" placeholder="URL" value="${c.url || ''}" oninput="updateChunk(${c.id}, 'url', this.value)">`;
            }
            
            card.innerHTML = html;
            container.appendChild(card);
        });
    }

    function updatePreview() {
        let email = `<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
                        <table align="center" width="600" style="border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;">`;

        chunks.forEach(c => {
            if (c.type === 'header') email += `<tr><td align="center" style="padding:20px; background-color:#EB178E; color:#ffffff;"><h1 style="margin:0; font-size:24px;">${c.val}</h1></td></tr>`;
            if (c.type === 'subheader') email += `<tr><td align="center" style="padding:20px; color:#EB178E;"><h2 style="margin:0; font-size:22px;">${c.val}</h2><div style="margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;"></div></td></tr>`;
            if (c.type === 'image') email += `<tr><td align="center" style="padding:20px;"><img src="${c.val}" width="100%" style="max-width:560px; border-radius:6px; display:block;"></td></tr>`;
            if (c.type === 'text'){
                const alignment = c.align || 'center';
                email += `<tr>
                            <td style="padding:20px; color:#333333; font-size:16px; line-height:1.5; text-align: ${alignment};">
                                <p><strong>${c.sub}</strong></p>
                                <p>${c.body}</p>
                            </td></tr>`;
            }
            if (c.type === 'event') email += `<tr><td style="padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;"><p><strong>${c.title}</strong></p><p>${c.info}</p><p style="margin:8px 0;">📅 <strong>Fecha:</strong> ${c.date}</p><p style="margin:8px 0;">⏰ <strong>Horario:</strong> ${c.time || 'Por determinar'}</p><p style="margin:8px 0;">📍 <strong>Lugar:</strong> ${c.place}</p></td></tr>`;
            if (c.type === 'button') email += `<tr><td align="center" style="padding:20px;"><a href="${c.url}" style="background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;">${c.text}</a></td></tr>`;
        });

        if (document.getElementById('show-footer').checked) {
            email += `<tr>
                            <td align="center" style="padding:0 20px;">
                                <table role="presentation" width="550" cellpadding="0" cellspacing="0" align = "center" border="0"
                                    style="border-top:5px solid #EB178E; margin-top:20px; padding-top:20px; font-family:Arial, sans-serif; font-size:14px; color:#555555;">
                                    <tr>
                                        <td style="margin-top:20px; padding:0px; color:#333333; font-size:16px; line-height:1.5;">
                                            <p>Para <strong>enterarte de todos los eventos, workshops y oportunidades</strong> te recomendamos que estés atento y nos sigas por:</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding:10px 0;">
                                        <!-- Web -->
                                        <a href="https://aiscmadrid.com/" target="_blank" style="margin:0 20px; display:inline-block;">

                                            <img src="https://aiscmadrid.com/images/logos/PNG/internet-rosa.png" alt="Button 1" width="32" height="32" border="0" style="display:block;">
                                        </a>
                                        <!-- Instagram -->
                                        <a href="https://www.instagram.com/aisc_madrid/" target="_blank" style="margin:0 20px; display:inline-block;">
                                            <img src="https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png" alt="Button 2" width="32" height="32" border="0" style="display:block;">
                                        </a>
                                        <!-- WhatsApp -->
                                        <a href="https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=wwt" target='_blank' style='margin:0 20px; display:inline-block;'>
                                            <img src="https://aiscmadrid.com/images/logos/PNG/whatsapp-rosa.png" alt="Button 3" width='32' height='32' border="0" style="display:block;">
                                        </a>
                                        <!-- LinkedIn -->
                                        <a href="https://www.linkedin.com/company/ai-student-collective-madrid/" target="_blank" style="margin:0 20px; display:inline-block;">
                                            <img src="https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png" alt="Button 4" width="32" height="32" border="0" style="display:block;">
                                        </a>
                                        </td>
                                    </tr>
                                    <!-- Logo footer-->
                                    <tr>
                                        <td align="center" style="padding:10px; padding-left:40px">
                                            <a href="https://aiscmadrid.com/" target="_blank" style="margin:0 20px; display:inline-block;">
                                                <img src="https://aiscmadrid.com/images/logos/PNG/AISCMadridLogoAndLetters.png" alt="Logo Footer" width="300" border="0" style="display:block;">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px; padding:10px;">
                                            <a href='https://aiscmadrid.com/index.php#newsletter' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>¿Te han reenviado la Newsletter? Suscríbete aquí</a>
                                            |
                                            <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=$unsubscribe_token' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>`;
        }

        email += `</table></body></html>`;
        document.getElementById('email-preview').innerHTML = email;
        document.getElementById('code-output').value = email;
    }

    function downloadHTML() {
        const htmlContent = document.getElementById('code-output').value;
        if (!htmlContent) return alert("Crea contenido primero");
        const blob = new Blob([htmlContent], { type: 'text/html' });
        const link = document.createElement("a");
        link.download = `newsletter_aisc_${new Date().toISOString().slice(0, 10)}.html`;
        link.href = window.URL.createObjectURL(blob);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    addChunk('header');
</script>

</body>
</html>