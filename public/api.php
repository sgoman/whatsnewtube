<?php
require_once __DIR__ . '/../src/Router.php';

$router = new Router();

header('Content-Type: application/json');

try {
    $response = $router->handleRequest($_SERVER['REQUEST_METHOD'], $_GET);
    echo $response;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>