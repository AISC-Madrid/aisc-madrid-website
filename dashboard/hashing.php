<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Generador de Hash — Asociación</title>
<style>
:root{--bg:#0f1724;--card:#0b1220;--accent:#6ee7b7;--muted:#94a3b8}
*{box-sizing:border-box;font-family:Inter,ui-sans-serif,system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial}
body{margin:0;background:linear-gradient(180deg,#071022 0%,var(--bg) 100%);color:#e6eef6;display:flex;align-items:center;justify-content:center;height:100vh}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));padding:28px;border-radius:14px;box-shadow:0 10px 30px rgba(2,6,23,0.6);width:420px}
header{display:flex;gap:12px;align-items:center;margin-bottom:18px}
.logo{width:56px;height:56px;border-radius:10px;overflow:hidden;flex:0 0 56px}
.logo img{width:100%;height:100%;object-fit:contain;background:#fff}
h1{font-size:18px;margin:0}
p.lead{margin:4px 0 0;color:var(--muted);font-size:13px}
form{margin-top:12px}
label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px}
input[type="password"]{width:100%;padding:10px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;font-size:14px}
.row{display:flex;gap:8px;margin-top:10px}
button{background:var(--accent);border:none;padding:10px 12px;border-radius:8px;color:#022;cursor:pointer;font-weight:600}
button.secondary{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--muted)}
.result{background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;margin-top:12px;font-size:13px;word-break:break-all}
.meta{font-size:12px;color:var(--muted);margin-top:10px}
.error{background:rgba(255,0,0,0.06);color:#ff9b9b;padding:8px;border-radius:6px;font-size:13px}
.small{font-size:12px;color:var(--muted)}
.actions{display:flex;gap:8px;align-items:center;margin-top:8px}
</style>
</head>
<body>
<div class="card" role="main">
<header>
<div class="logo" title="Logo de la asociación">
<!-- Sustituye 'logo.png' por la ruta de tu logo -->
<img src="/images/logos/PNG/AISC Logo Color.png" alt="Logo AISC Madrid">
</div>
<div>
<h1>Generador de hash</h1>
<p class="lead">Introduce una contraseña y genera su hash seguro (one‑way).</p>
</div>
</header>


<?php if ($error): ?>
<div class="error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>


<form method="post" autocomplete="off" novalidate>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">


<label for="password">Contraseña a hashear</label>
<input id="password" name="password" type="password" placeholder="Introduce la contraseña" aria-label="Contraseña">


</html>