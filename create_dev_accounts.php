<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=form_db', 'kamal', 'kamal233.com');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$password = 'password';
$hash = password_hash($password, PASSWORD_BCRYPT);

$names = [
    'admin' => 'Admin User',
    'super_admin' => 'Super Admin User',
    'developer' => 'Developer User',
    'commercial' => 'Commercial User',
];

foreach ($names as $role => $name) {
    $pdo->prepare('
        INSERT INTO users (name, email, password, role, is_active, email_verified_at)
        VALUES (?, ?, ?, ?, 1, NOW())
    ')->execute([$name, strtolower(str_replace(' ', '.', $name)).'@example.com', $hash, $role]);
    echo "Created: $name (role: $role)\n";
}

echo "\nAll accounts created with password: $password\n";
