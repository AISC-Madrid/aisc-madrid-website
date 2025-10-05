<?php
// Archivo: hash_password_web.php
// Versión minimalista: escribe la contraseña y muestra su hash.

$original = '';
$hash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original = isset($_POST['password']) ? trim($_POST['password']) : '';
    if ($original === '') {
        $error = 'Introduce una contraseña.';
    } else {
        $hash = password_hash($original, PASSWORD_DEFAULT);
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generador de Hash</title>
    <style>
        body { font-family: sans-serif; background: #f8f8f8; color: #222; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 6px; width: 340px; text-align: center; }
        img { height: 40px; margin-bottom: 10px; }
        input[type="text"] { width: 100%; padding: 8px; margin-top: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 10px; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; background: #eee; cursor: pointer; }
        .result { margin-top: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa; font-family: monospace; word-break: break-all; text-align: left; }
        .error { color: #b00; margin-top: 8px; font-size: 0.9em; }
        .note { font-size: 0.8em; color: #555; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <img src="logo.png" alt="Logo" onerror="this.style.display='none'">
        <h1>Generador de Hash</h1>
        <img src="/images/logos/PNG/AISC Logo Color.png" alt="AISC Logo" style="height: 60px; margin-bottom: 10px;">
        <form method="post" autocomplete="off">
            <input name="password" type="text" placeholder="Introduce la contraseña">
            <button type="submit">Generar</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($hash): ?>
            <div class="result">
                <div><strong>Original:</strong> <?= htmlspecialchars($original, ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Hash:</strong> <?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
