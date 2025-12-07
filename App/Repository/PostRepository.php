<?php
namespace App\Repository;

use App\Database;
use PDO;

class PostRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findAll($status = null) {
        $sql = "SELECT * FROM posts";
        if ($status) {
            $sql .= " WHERE status = :status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($titulo, $conteudo, $autorId, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO posts (titulo, conteudo, autor_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $conteudo, $autorId, $status]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $dados) {
        $fields = [];
        $params = [];

        foreach ($dados as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        if (empty($fields)) return false;

        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}