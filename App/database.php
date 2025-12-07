<?php
// configuração do banco de dados do php

namespace App;

use PDO;
use PDOException;

class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            try {
                // Caminho absoluto para o arquivo do banco
                $dbPath = __DIR__ . '/../blog_simples.sqlite';
                self::$pdo = new PDO("sqlite:" . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                // Habilita chaves estrangeiras no SQLite
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
            } catch (PDOException $e) {
                die(json_encode(["erro" => "Falha na conexão: " . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}


?> 