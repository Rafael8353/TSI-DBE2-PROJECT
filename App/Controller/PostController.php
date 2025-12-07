<?php
namespace App\Controller;

use App\Service\PostService;

class PostController {
    private $service;

    public function __construct() {
        $this->service = new PostService();
    }

    public function handleRequest($method, $id = null) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            switch ($method) {
                case 'GET':
                    if ($id) {
                        echo json_encode($this->service->buscarPorId($id));
                    } else {
                        // Captura filtro da URL (ex: ?status=publicado) [cite: 39]
                        $status = $_GET['status'] ?? null;
                        echo json_encode($this->service->listarTodos($status));
                    }
                    break;

                case 'POST':
                    http_response_code(201);
                    echo json_encode($this->service->criar($input));
                    break;

                case 'PUT':
                case 'PATCH':
                    if (!$id) throw new \Exception("ID necessário", 400);
                    echo json_encode($this->service->atualizar($id, $input));
                    break;

                case 'DELETE':
                    if (!$id) throw new \Exception("ID necessário", 400);
                    echo json_encode($this->service->deletar($id));
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(["erro" => "Método não permitido"]);
            }
        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(["erro" => $e->getMessage()]);
        }
    }
}