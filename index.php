<?php
// index.php
header("Content-Type: application/json; charset=UTF-8");
require 'db.php';

// Captura método e caminho
$method = $_SERVER['REQUEST_METHOD'];
// Simulação simples de roteamento (pega o que vem depois de /api/)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Pega dados do corpo da requisição (JSON)
$input = json_decode(file_get_contents('php://input'), true);

// ROTA: GET / (Apresentação da API) 
if ($path == '/' || empty($pathParts[0])) {
    echo json_encode([
        "api" => "Blog Simples Acadêmico",
        "autores" => "Leonardo Ennes, Rafael Gonçales",
        "mensagem" => "Bem-vindo à API. Use /api/usuarios ou /api/posts"
    ]);
    exit;
}

// Verifica se é uma rota da API
if ($pathParts[0] !== 'api') {
    http_response_code(404);
    echo json_encode(["erro" => "Rota não encontrada"]);
    exit;
}

$resource = $pathParts[1] ?? null;
$id = $pathParts[2] ?? null;
$subResource = $pathParts[3] ?? null; // Para rotas como /usuarios/{id}/posts

// --- ROTAS DE USUÁRIOS  ---
if ($resource === 'usuarios') {
    
    // GET /api/usuarios e /api/usuarios/{id}
    if ($method === 'GET') {
        if ($id && $subResource === 'posts') {
            // Rota Extra: /api/usuarios/{id}/posts [cite: 39]
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE autor_id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetchAll());
        } elseif ($id) {
            // Busca usuário específico
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch() ?: ["erro" => "Usuário não encontrado"]);
        } else {
            // Lista todos
            $stmt = $pdo->query("SELECT * FROM usuarios");
            echo json_encode($stmt->fetchAll());
        }
    }
    
    // POST /api/usuarios (Cria usuário)
    elseif ($method === 'POST') {
        // Validação: Nome e email obrigatórios [cite: 30]
        if (empty($input['nome']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e email são obrigatórios."]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
            $stmt->execute([$input['nome'], $input['email']]);
            echo json_encode(["mensagem" => "Usuário criado", "id" => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            // Tratamento para email único [cite: 31]
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(["erro" => "Email já cadastrado."]);
            } else {
                http_response_code(500);
                echo json_encode(["erro" => $e->getMessage()]);
            }
        }
    }

    // PUT e PATCH /api/usuarios/{id} (Atualiza usuário)
    elseif (($method === 'PUT' || $method === 'PATCH') && $id) {
        // Lógica simplificada: constrói a query dinamicamente
        $fields = [];
        $params = [];
        
        if (isset($input['nome'])) { $fields[] = "nome = ?"; $params[] = $input['nome']; }
        if (isset($input['email'])) { $fields[] = "email = ?"; $params[] = $input['email']; }
        
        if (empty($fields)) {
            echo json_encode(["erro" => "Nenhum dado enviado."]);
            exit;
        }

        $params[] = $id;
        $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(["mensagem" => "Usuário atualizado."]);
    }

    // DELETE /api/usuarios/{id}
    elseif ($method === 'DELETE' && $id) {
        /* Nota: A regra sobre "não deletar o único administrador" [cite: 32] 
           não pode ser implementada totalmente pois a tabela 'usuarios' [cite: 8-11] 
           não possui um campo de 'perfil' ou 'is_admin'. Seguiremos com a deleção padrão. */
        
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["mensagem" => "Usuário deletado (e seus posts, se existirem)."]);
    }
}

// --- ROTAS DE POSTS [cite: 39] ---
elseif ($resource === 'posts') {

    // GET /api/posts (Com filtro de status opcional)
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch() ?: ["erro" => "Post não encontrado"]);
        } else {
            // Filtro por querystring ?status= [cite: 39]
            $status = $_GET['status'] ?? null;
            if ($status) {
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE status = ?");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query("SELECT * FROM posts");
            }
            echo json_encode($stmt->fetchAll());
        }
    }

    // POST /api/posts (Cria post)
    elseif ($method === 'POST') {
        // Validação: Título e conteúdo obrigatórios [cite: 33]
        if (empty($input['titulo']) || empty($input['conteudo']) || empty($input['autor_id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Titulo, conteudo e autor_id são obrigatórios."]);
            exit;
        }

        // Verifica se autor existe [cite: 34]
        $checkAutor = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $checkAutor->execute([$input['autor_id']]);
        if (!$checkAutor->fetch()) {
            http_response_code(400);
            echo json_encode(["erro" => "Autor_id inválido ou inexistente."]);
            exit;
        }

        // Define status padrão como 'rascunho' se não enviado 
        $status = $input['status'] ?? 'rascunho';
        
        // Validação de status permitido [cite: 36]
        if (!in_array($status, ['rascunho', 'publicado'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Status inválido. Use 'rascunho' ou 'publicado'."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO posts (titulo, conteudo, autor_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$input['titulo'], $input['conteudo'], $input['autor_id'], $status]);
        
        echo json_encode(["mensagem" => "Post criado", "id" => $pdo->lastInsertId(), "status" => $status]);
    }

    // PUT e PATCH /api/posts/{id}
    elseif (($method === 'PUT' || $method === 'PATCH') && $id) {
        $fields = [];
        $params = [];

        if (isset($input['titulo'])) { $fields[] = "titulo = ?"; $params[] = $input['titulo']; }
        if (isset($input['conteudo'])) { $fields[] = "conteudo = ?"; $params[] = $input['conteudo']; }
        if (isset($input['autor_id'])) { $fields[] = "autor_id = ?"; $params[] = $input['autor_id']; }
        if (isset($input['status'])) { 
            // Valida status na atualização [cite: 36]
            if (!in_array($input['status'], ['rascunho', 'publicado'])) {
                echo json_encode(["erro" => "Status inválido."]);
                exit;
            }
            $fields[] = "status = ?"; 
            $params[] = $input['status']; 
        }

        if (empty($fields)) {
            echo json_encode(["erro" => "Nenhum dado enviado."]);
            exit;
        }

        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(["mensagem" => "Post atualizado."]);
    }

    // DELETE /api/posts/{id}
    elseif ($method === 'DELETE' && $id) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["mensagem" => "Post deletado."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["erro" => "Recurso não encontrado"]);
}
?>