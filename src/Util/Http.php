<?php
declare(strict_types=1);
namespace App\Util;

class Http
{
    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) $data = [];
        return $data;
    }
}
