<?php

echo "What is the password you want to hash? ";

$password = rtrim(fgets(STDIN), "\r\n");

if ($password === null || $password === '') {
    fwrite(STDERR, "No password entered. Exiting.\n");
    exit(1);
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "Original password: " . $password . PHP_EOL;
echo "Generated hash: " . $hashed . PHP_EOL;
