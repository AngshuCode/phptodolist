<?php
declare(strict_types=1);
namespace App;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $rel = substr($class, strlen($prefix));
        $path = __DIR__ . '/' . str_replace('\\', '/', $rel) . '.php';
        if (is_file($path)) require $path;
    }
});

// Ensure SQLite database exists
$basePath = dirname(__DIR__);
$dbPath = $basePath . '/data/todos.sqlite';
$init = !file_exists($dbPath);
$pdo = new \PDO('sqlite:' . $dbPath, null, null, [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
]);

if ($init) {
    $pdo->exec('CREATE TABLE todos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "open",
        tags TEXT,
        due_at TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
}

// Globally accessible container
class Container { public static \PDO $db; }
Container::$db = $pdo;
