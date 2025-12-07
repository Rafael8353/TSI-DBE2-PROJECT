<?php
namespace App\Service;

use App\Repository\UsuarioRepository;

class UsuarioService {
    private $repository;

    public function __construct() {
        $this->repository = new UsuarioRepository();
    }

    public function listarTodos() {
        return $this->repository->findAll();
    }

    public function buscarPorId($id) {
        $user = $this->repository->findById($id);
        if (!$user) {
            throw new \Exception("Usuário não encontrado", 404);
        }
        return $user;
    }

    public function criar($dados) {
        if (empty($dados['nome']) || empty($dados['email'])) {
            throw new \Exception("Nome e email são obrigatórios.", 400);
        }

        if ($this->repository->findByEmail($dados['email'])) {
            throw new \Exception("Email já cadastrado.", 409);
        }

        $id = $this->repository->create($dados['nome'], $dados['email']);
        return ["mensagem" => "Usuário criado", "id" => $id];
    }

    public function deletar($id) {
        $this->buscarPorId($id); // Verifica se existe
        $this->repository->delete($id);
        return ["mensagem" => "Usuário deletado."];
    }
}