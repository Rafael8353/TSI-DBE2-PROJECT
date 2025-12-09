<?php


$dbFile = __DIR__ . '/blog_simples.sqlite';


if (file_exists($dbFile)) {
    unlink($dbFile);
}

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON;");

 
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE
    )");

  
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        conteudo TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'rascunho',
        autor_id INTEGER NOT NULL,
        FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

   
    $pdo->exec("INSERT INTO usuarios (nome, email) VALUES ('Leonardo', 'leo@email.com')");
    $pdo->exec("INSERT INTO usuarios (nome, email) VALUES ('Rafael', 'rafa@email.com')");
    
    echo "Sucesso! Banco de dados 'blog_simples.sqlite' criado.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}