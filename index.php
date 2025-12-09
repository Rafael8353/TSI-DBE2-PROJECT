<?php

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


$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);


$scriptName = str_replace('\\', '/', $scriptName); 

$path = str_replace($scriptName, '', $requestUri);


$pathParts = explode('/', trim($path, '/'));


if (empty($pathParts[0])) {
    echo json_encode([
        "api" => "Blog Simples Acadêmico",
        "autores" => "Leonardo Ennes, Rafael Gonçales",
        "rotas" => ["/api/usuarios", "/api/posts"]
    ]);
    exit;
}


if ($pathParts[0] !== 'api') {
    http_response_code(404);

    echo json_encode([
        "erro" => "Endpoint não encontrado.",
        "detalhe" => "Esperava '/api/...', recebeu '/" . $pathParts[0] . "...'",
        "path_calculado" => $path
    ]);
    exit;
}


$resource = $pathParts[1] ?? null;
$id = $pathParts[2] ?? null;


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