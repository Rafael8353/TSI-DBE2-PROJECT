<?php
// Autoload manual
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/App/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

header("Content-Type: application/json; charset=UTF-8");

// --- Lógica de Roteamento Ajustada (CORRIGIDO PARA WINDOWS) ---
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// [CORREÇÃO] Normaliza as barras para o padrão do navegador (/)
// Isso resolve o problema do dirname retornar "\Blog" no Windows
$scriptName = str_replace('\\', '/', $scriptName); 

// Remove a pasta do projeto da URL para pegar apenas o caminho da API
// Se a URL for /Blog/api/usuarios, $path vira /api/usuarios
$path = str_replace($scriptName, '', $requestUri);

// Explode e remove itens vazios
$pathParts = explode('/', trim($path, '/'));

// ROTA: Apresentação (GET /)
// Se não tiver nada depois da pasta do projeto (ex: localhost/Blog/)
if (empty($pathParts[0])) {
    echo json_encode([
        "api" => "Blog Simples Acadêmico",
        "autores" => "Leonardo Ennes, Rafael Gonçales",
        "rotas" => ["/api/usuarios", "/api/posts"]
    ]);
    exit;
}

// Verifica prefixo 'api'
if ($pathParts[0] !== 'api') {
    http_response_code(404);
    // Debug: Mostra o que ele recebeu para ajudar a entender erros futuros
    echo json_encode([
        "erro" => "Endpoint não encontrado.",
        "detalhe" => "Esperava '/api/...', recebeu '/" . $pathParts[0] . "...'",
        "path_calculado" => $path
    ]);
    exit;
}

// Remove o 'api' do array para processar o resto (recurso e ID)
// Agora $pathParts[1] será o recurso
$resource = $pathParts[1] ?? null;
$id = $pathParts[2] ?? null;

// Dispatcher
try {
    if ($resource === 'usuarios') {
        $controller = new \App\Controller\UsuarioController();
        $controller->handleRequest($_SERVER['REQUEST_METHOD'], $id);
    } elseif ($resource === 'posts') {
        $controller = new \App\Controller\PostController();
        $controller->handleRequest($_SERVER['REQUEST_METHOD'], $id);
    } else {
        throw new Exception("Recurso '$resource' não encontrado", 404);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["erro" => $e->getMessage()]);
}