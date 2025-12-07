<?php
// db.php
$host = 'localhost';
$db   = 'blog_simples';
$user = 'root'; // Ajuste conforme seu usuário
$pass = '';     // Ajuste conforme sua senha

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(["erro" => "Falha na conexão: " . $e->getMessage()]));
}
?>