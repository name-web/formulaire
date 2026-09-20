<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=form_db', 'kamal', 'kamal233.com');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SELECT id, name, role, is_active FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo $u['name'].' ('.$u['role'].') active: '.($u['is_active'] ? 'yes' : 'no')."\n";
}
echo 'Total users: '.count($users)."\n";
