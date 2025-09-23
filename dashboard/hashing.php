<?php

$password = "Polly2000";

$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña original: " . $password . PHP_EOL;
echo "Hash generado: " . $hashed . PHP_EOL;
