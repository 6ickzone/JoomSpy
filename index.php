<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JoomSpy — Passive Attack Surface Mapping</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }
body { background:#0a0a0a; color:#e5e5e5; min-height:100vh; padding:40px 20px; display:flex; justify-content:center; }
.container { width:100%; max-width:900px; }
header { margin-bottom:32px; border-bottom:1px solid #1a1a1a; padding-bottom:16px; }
h1 { font-size:22px; font-weight:600; color:#00f0ff; letter-spacing:0.5px; display:flex; align-items:center; gap:8px; }
h1 small { font-size:12px; color:#666; font-weight:400; background:#111; border:1px solid #222; padding:2px 8px; border-radius:4px; }
.sub { font-size:12px; color:#666; margin-top:4px; }
.control-panel { background:#111; border:1px solid #1a1a1a; border-radius:6px; padding:20px; margin-bottom:20px; }
.input-group { display:flex; gap:10px; width:100%; }
input[type="text"] { flex:1; padding:12px 14px; background:#000000; border:1px solid #222; border-radius:4px; color:#fff; font-size:13px; font-family:monospace; transition:all .2s ease; }
input[type="text"]:focus { border-color:#00f0ff; outline:none; box-shadow:0 0 10px rgba(0,240,255,0.05); }
.btn { padding:0 24px; border:none; border-radius:4px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s ease; display:flex; align-items:center; }
.btn-primary { background:#fff; color:#000; }
.btn-primary:hover { background:#00f0ff; }
.btn-danger { background:#1a1a1a; color:#ff3333; border:1px solid #222; }
.btn-danger:hover { background:#ff3333; color:#fff; border-color:#ff3333; }
.btn:disabled { opacity:.3; cursor:not-allowed; background:#111 !important; color:#444 !important; border-color:#222 !important; }
.stats-grid { display:flex; gap:12px; margin-top:16px; flex-wrap:wrap; }
.stat-box { background:#000; border:1px solid #1a1a1a; padding:10px 16px; border-radius:4px; flex:1; min-width:120px; }
.stat-box .label { font-size:11px; color:#555; text-transform:uppercase; letter-spacing:0.5px; }
.stat-box .value { font-size:18px; font-weight:700; margin-top:2px; font-family:monospace; }
.cyan-text { color:#00f0ff; }
.yellow-text { color:#ffb703; }
#loadingStatus { text-align:center; padding:30px 0; background:#111; border:1px solid #1a1a1a; border-radius:6px; margin-bottom:20px; }
.spinner { display:inline-block; width:18px; height:18px; border:2px solid rgba(0,240,255,0.1); border-radius:50%; border-top-color:#00f0ff; animation:spin 0.8s linear infinite; vertical-align:middle; }
@keyframes spin { to { transform:rotate(360deg); } }
.spinner-text { font-size:12px; font-family:monospace; color:#00f0ff; margin-left:10px; }
.table-card { background:#111; border:1px solid #1a1a1a; border-radius:6px; padding:16px; }
.filter-wrapper { margin-bottom:14px; }
.filter-wrapper input { width:100%; max-width:260px; padding:8px 12px; background:#000; border:1px solid #222; border-radius:4px; color:#fff; font-size:12px; }
table { width:100%; border-collapse:collapse; font-size:12px; text-align:left; }
th { padding:12px 10px; border-bottom:2px solid #1a1a1a; color:#555; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; }
td { padding:12px 10px; border-bottom:1px solid #151515; vertical-align:top; line-height:1.4; }
.badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:600; font-family:monospace; }
.badge-comp { background:#002b47; color:#58a6ff; }
.badge-leak { background:#3d0808; color:#f85149; }
.badge-core { background:#0f2b15; color:#3fb950; }
.status-200 { font-weight:600; font-family:monospace; }
.status-403 { font-weight:600; font-family:monospace; }
.status-null { font-family:monospace; }
</style>
</head>
<body>

<div class="container">
    <header>
        <h1>JoomSpy</h1>
        <div class="sub">Passive Attack Surface Mapping</div>
    </header>

    <div class="control-panel">
        <div class="input-group">
            <input type="text" id="targetUrl" placeholder="https://target-instance.com" autocomplete="off">
            <button class="btn btn-primary" id="scanBtn">START</button>
            <button class="btn btn-danger" id="stopBtn" disabled>STOP</button>
        </div>
        
        <div class="stats-grid">
            <div class="stat-box"><div class="label">Total Vectors</div><div class="value" id="sMax">0</div></div>
            <div class="stat-box"><div class="label">Processed</div><div class="value" id="sTotal">0</div></div>
            <div class="stat-box"><div class="label">Detected (200)</div><div class="value cyan-text" id="sFound">0</div></div>
            <div class="stat-box"><div class="label">Restricted / Redirect</div><div class="value yellow-text" id="sRestricted">0</div></div>
        </div>
    </div>

    <div id="loadingStatus" style="display:none;">
        <div class="spinner"></div>
        <span class="spinner-text">L O A D I N G...</span>
    </div>

    <div class="table-card" id="resultCard" style="display:none;">
        <div class="filter-wrapper">
            <input type="text" id="filterInput" placeholder="Filter rows...">
        </div>
        <table style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width:45px">#</th>
                    <th style="width:110px">Type</th>
                    <th style="width:300px">Resource Definition</th>
                    <th>Methodology</th>
                    <th style="width:140px">Status</th>
                </tr>
            </thead>
            <tbody id="resultBody"></tbody>
        </table>
    </div>
</div>

<script>
const AUDIT_DB = [
    // === CORE INFO & API DISCOVERY ===
    { type: 'Core Info', name: 'Joomla API Config — App Settings', path: '/api/index.php/v1/config/application', method: 'API Discovery' },
    { type: 'Core Info', name: 'Joomla API Users — Enumeration', path: '/api/index.php/v1/users', method: 'API Discovery' },
    { type: 'Core Info', name: 'Joomla API Media — Files List', path: '/api/index.php/v1/media/files', method: 'API Discovery' },
    { type: 'Core Info', name: 'Joomla Version Fingerprint (XML)', path: '/administrator/manifests/files/joomla.xml', method: 'Version Enumeration' },
    { type: 'Core Info', name: 'Joomla Installation Setup Wizard', path: '/installation/index.php', method: 'Deployment Check' },

    // === CRITICAL DATA LEAK ===
    { type: 'Data Leak', name: 'Environment Config — .env', path: '/.env', method: 'Credential Leak Check' },
    { type: 'Data Leak', name: 'Configuration Backup (.bak)', path: '/configuration.php.bak', method: 'Config Leak Check' },
    { type: 'Data Leak', name: 'Configuration Backup (.old)', path: '/configuration.php.old', method: 'Config Leak Check' },
    { type: 'Data Leak', name: 'Git Repository — HEAD Ref', path: '/.git/HEAD', method: 'SCM Exposure Check' },
    { type: 'Data Leak', name: 'Exposed PHPInfo Script', path: '/phpinfo.php', method: 'Info Disclosure' },
    { type: 'Data Leak', name: 'Core Server Logs (J3+ /logs/)', path: '/logs/', method: 'Dir Listing Probe' },
    { type: 'Data Leak', name: 'Core Temporary Directory (/tmp/)', path: '/tmp/', method: 'Dir Listing Probe' },

    // === com_media (FILE MANAGER INTERACTIONS) ===
    { type: 'Component', name: 'com_media — Images Layout (No Auth)', path: '/index.php?option=com_media&view=images&tmpl=component&fieldid=&e_name=jform_articletext&asset=com_content&author=&folder=', method: 'MVC Context Audit' },
    { type: 'Component', name: 'com_media — File Upload Endpoint', path: '/index.php?option=com_media&task=file.upload&tmpl=component', method: 'Interface Validation' },
    { type: 'Component', name: 'com_media — File Delete Endpoint', path: '/index.php?option=com_media&task=file.delete&tmpl=component', method: 'Interface Validation' },
    { type: 'Component', name: 'com_media — Folder Create Endpoint', path: '/index.php?option=com_media&task=folder.create&tmpl=component', method: 'Interface Validation' },

    // === com_jce & CRITICAL FILE MANAGERS ===
    { type: 'Component', name: 'com_jce — Profiles Import Endpoint', path: '/index.php?option=com_jce&task=profiles.import', method: 'MVC Task Audit' },
    { type: 'Component', name: 'com_jce — XML Manifest Fingerprint', path: '/administrator/components/com_jce/jce.xml', method: 'Manifest Validation' },
    { type: 'Component', name: 'com_jdownloads — Upload Interface', path: '/index.php?option=com_jdownloads&task=upload', method: 'Interface Validation' },
    { type: 'Component', name: 'com_rsform — AJAX Submit (File Upload)', path: '/index.php?option=com_rsform&task=ajax.submit', method: 'Interface Validation' },

    // === CRITICAL COMPONENTS (SQLi / XSS PROBES) ===
    { type: 'Component', name: 'com_fabrik — Table Data (SQLi Context)', path: '/index.php?option=com_fabrik&view=table&tableid=1', method: 'Parameter Probing' },
    { type: 'Component', name: 'com_k2 — Item View (SQLi Context)', path: '/index.php?option=com_k2&view=item&id=1', method: 'Parameter Probing' },
    { type: 'Component', name: 'com_k2 — User Enumeration Interface', path: '/index.php?option=com_k2&view=itemlist&task=user&id=1', method: 'User Enumeration' },
    { type: 'Component', name: 'com_sppagebuilder — AJAX Endpoint (SQLi Context)', path: '/index.php?option=com_sppagebuilder&task=ajax&format=raw', method: 'Parameter Probing' },
    { type: 'Component', name: 'com_virtuemart — Product Detail (SQLi Context)', path: '/index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=1', method: 'Parameter Probing' },
    { type: 'Component', name: 'com_hikashop — Checkout (XSS Context)', path: '/index.php?option=com_hikashop&view=checkout&ctrl=checkout', method: 'Parameter Probing' },
    { type: 'Component', name: 'com_akeeba — Backup Download Interface', path: '/index.php?option=com_akeeba&view=backup', method: 'Backup Discovery' },

    // === AC / ADMINISTRATIVE ACCESS CHECKS ===
    { type: 'Access Check', name: 'Administrative Control Panel Portal', path: '/administrator/index.php', method: 'Portal Presence Check' },
    { type: 'Access Check', name: 'User Registration Interface Status', path: '/index.php?option=com_users&view=registration', method: 'Auth Policy Audit' },
    { type: 'Access Check', name: 'User Profile Dashboard Context', path: '/index.php?option=com_users&view=profile', method: 'Auth Policy Audit' },
    { type: 'Access Check', name: 'Administrative Global Configuration Access', path: '/administrator/index.php?option=com_config', method: 'Privilege Exposure Check' },
    { type: 'Access Check', name: 'Core Template Style Interface Check', path: '/index.php?option=com_templates&view=template&id=506', method: 'Privilege Exposure Check' }
];


const state = { running:false, stopped:false, total:0, found:0, restricted:0 };
document.getElementById('sMax').textContent = AUDIT_DB.length;

function getTagClass(type) {
    if (type === 'Component') return 'badge-comp';
    if (type === 'Data Leak') return 'badge-leak';
    return 'badge-core';
}

function addRow(idx, type, name, path, method, status, baseTarget) {
    const tbody = document.getElementById('resultBody');
    const row = document.createElement('tr');
    row.dataset.search = (type + ' ' + name + ' ' + path + ' ' + status).toLowerCase();
    
    let statusClass = 'status-null';
    let statusColor = '#444';

    if (status.includes('200')) {
        statusClass = 'status-200';
        statusColor = '#00f0ff';
    } else if (status.includes('403') || status.includes('302')) {
        statusClass = 'status-403';
        statusColor = '#ffb703';
    }

    const fullUrl = baseTarget + path;
   
    const statusHtml = `<a href="${fullUrl}" target="_blank" style="color: ${statusColor}; text-decoration: underline; cursor: pointer; font-weight: 600;">${status}</a>`;

    row.innerHTML = `
        <td style="color:#444;">${idx}</td>
        <td><span class="badge ${getTagClass(type)}">${type}</span></td>
        <td style="word-wrap:break-word;"><strong>${name}</strong><br><span style="color:#555; font-family:monospace; font-size:11px;">${path}</span></td>
        <td style="color:#777;">${method}</td>
        <td class="${statusClass}">${statusHtml}</td>
    `;
    tbody.appendChild(row);
    document.getElementById('resultCard').style.display = 'block';
}

async function startAudit() {
    const base = document.getElementById('targetUrl').value.replace(/\/+$/, '');
    if (!base || !base.startsWith('http')) { alert('Provide a valid target URL.'); return; }

    state.running = true; state.stopped = false; state.total = 0; state.found = 0; state.restricted = 0;
    document.getElementById('resultBody').innerHTML = '';
    
    document.getElementById('scanBtn').disabled = true;
    document.getElementById('stopBtn').disabled = false;
    document.getElementById('loadingStatus').style.display = 'block';

    for (let i = 0; i < AUDIT_DB.length; i++) {
        if (state.stopped) break;
        const item = AUDIT_DB[i];
        
        let displayStatus = 'NOT DETECTED (404)';
        
        try {
            const response = await fetch(`scan.php?url=${encodeURIComponent(base)}&path=${encodeURIComponent(item.path)}`, {
                signal: AbortSignal.timeout(8000)
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    if (data.http_code === 200) {
                        displayStatus = 'ACCESSIBLE (200)';
                        state.found++;
                    } else if (data.http_code === 403) {
                        displayStatus = 'RESTRICTED (403)';
                        state.restricted++;
                    } else if (data.http_code === 302 || data.http_code === 301) {
                        displayStatus = `REDIRECT (${data.http_code})`;
                        state.restricted++;
                    } else {
                        displayStatus = `NOT DETECTED (${data.http_code})`;
                    }
                } else {
                    displayStatus = 'TIMEOUT/ERROR';
                }
            } else {
                displayStatus = 'SERVER ERROR';
            }
        } catch (e) {
            displayStatus = e.name === 'AbortError' ? 'TIMEOUT' : 'OFFLINE/BLOCKED';
        }
        
        state.total++;
        addRow(i + 1, item.type, item.name, item.path, item.method, displayStatus, base);
        
        document.getElementById('sTotal').textContent = state.total;
        document.getElementById('sFound').textContent = state.found;
        document.getElementById('sRestricted').textContent = state.restricted;
        
        await new Promise(r => setTimeout(r, 1000)); 
    }

    document.getElementById('loadingStatus').style.display = 'none';
    document.getElementById('scanBtn').disabled = false;
    document.getElementById('stopBtn').disabled = true;
}

document.getElementById('filterInput').addEventListener('input', () => {
    const query = document.getElementById('filterInput').value.toLowerCase();
    document.querySelectorAll('#resultBody tr').forEach(row => {
        row.style.display = row.dataset.search.includes(query) ? '' : 'none';
    });
});

document.getElementById('scanBtn').addEventListener('click', startAudit);
document.getElementById('stopBtn').addEventListener('click', () => {
    state.stopped = true;
    document.getElementById('loadingStatus').style.display = 'none';
    document.getElementById('scanBtn').disabled = false;
    document.getElementById('stopBtn').disabled = true;
});
</script>
</body>
</html>
