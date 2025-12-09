<?php
namespace App\Service;

use App\Repository\PostRepository;
use App\Repository\UsuarioRepository;

class PostService {
    private $postRepo;
    private $userRepo;

    public function __construct() {
        $this->postRepo = new PostRepository();
        $this->userRepo = new UsuarioRepository();
    }

    public function listarTodos($statusFiltro = null) {
    
        return $this->postRepo->findAll($statusFiltro);
    }

    public function buscarPorId($id) {
        $post = $this->postRepo->findById($id);
        if (!$post) throw new \Exception("Post não encontrado", 404);
        return $post;
    }

    public function criar($dados) {
       
        if (empty($dados['titulo']) || empty($dados['conteudo']) || empty($dados['autor_id'])) {
            throw new \Exception("Titulo, conteudo e autor_id são obrigatórios.", 400);
        }

      
        if (!$this->userRepo->findById($dados['autor_id'])) {
            throw new \Exception("Autor inválido ou inexistente.", 400);
        }

        
        $status = $dados['status'] ?? 'rascunho';
        if (!in_array($status, ['rascunho', 'publicado'])) {
            throw new \Exception("Status inválido. Use 'rascunho' ou 'publicado'.", 400);
        }

        $id = $this->postRepo->create($dados['titulo'], $dados['conteudo'], $dados['autor_id'], $status);
        return ["mensagem" => "Post criado", "id" => $id, "status" => $status];
    }

    public function atualizar($id, $dados) {
        $this->buscarPorId($id); 

      
        if (isset($dados['status']) && !in_array($dados['status'], ['rascunho', 'publicado'])) {
            throw new \Exception("Status inválido.", 400);
        }
        
      
        $dadosParaUpdate = array_filter($dados, function($v) { return !is_null($v); });

        $this->postRepo->update($id, $dadosParaUpdate);
        return ["mensagem" => "Post atualizado."];
    }

    public function deletar($id) {
        $this->buscarPorId($id);
        $this->postRepo->delete($id);
        return ["mensagem" => "Post deletado."];
    }
}