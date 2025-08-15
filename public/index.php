<?php
// Simple PHP + SQLite Todo App with JSON API and lightweight UI
// Router and front controller

declare(strict_types=1);

// Error reporting for dev
ini_set('display_errors', '1');
error_reporting(E_ALL);

// CORS for API (adjust for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$basePath = dirname(__DIR__);
require_once $basePath . '/src/bootstrap.php';

use App\Controller\TodoController;
use App\Util\Http;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$controller = new TodoController();

// Simple routing
if (str_starts_with($path, '/api/todos')) {
    $id = null;
    if (preg_match('#^/api/todos/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
    }
    switch ($method) {
        case 'GET':
            if ($id) {
                Http::json($controller->get($id));
            } else {
                $query = [
                    'q' => $_GET['q'] ?? null,
                    'status' => $_GET['status'] ?? null,
                    'sort' => $_GET['sort'] ?? null,
                ];
                Http::json($controller->list($query));
            }
            break;
        case 'POST':
            $payload = Http::jsonBody();
            Http::json($controller->create($payload), 201);
            break;
        case 'PUT':
        case 'PATCH':
            if (!$id) Http::json(['error' => 'Missing id'], 400);
            $payload = Http::jsonBody();
            Http::json($controller->update($id, $payload));
            break;
        case 'DELETE':
            if (!$id) Http::json(['error' => 'Missing id'], 400);
            Http::json($controller->delete($id));
            break;
        default:
            Http::json(['error' => 'Method not allowed'], 405);
    }
    exit;
}

// Serve UI
if ($path === '/' || $path === '/index.html') {
    readfile(__DIR__ . '/app.html');
    exit;
}

// Static assets fallback
$asset = realpath(__DIR__ . $path);
if ($asset && str_starts_with($asset, realpath(__DIR__))) {
    if (is_file($asset)) {
        $ext = pathinfo($asset, PATHINFO_EXTENSION);
        $types = [
            'js' => 'text/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        if (isset($types[$ext])) header('Content-Type: ' . $types[$ext]);
        readfile($asset);
        exit;
    }
}

http_response_code(404);
echo 'Not Found';
