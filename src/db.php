<?php
$dsn = 'mysql:host=localhost;dbname=bookshop;charset=utf8';
$username = 'root';
$password = 'SetRootPasswordHere';


try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
