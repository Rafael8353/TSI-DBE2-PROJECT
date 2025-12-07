<?php
namespace App\Repository;

use App\Database;
use PDO;

class UsuarioRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($nome, $email) {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
        return $this->pdo->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}