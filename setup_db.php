<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS store");
    echo "Database 'store' created or checked successfully.\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    exit(1);
}
