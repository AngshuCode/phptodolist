<?php
declare(strict_types=1);

namespace App\Controller;

use App\Container;
use PDO;

class TodoController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Container::$db;
    }

    public function list(array $query): array
    {
        $sql = 'SELECT * FROM todos WHERE 1=1';
        $params = [];
        if (!empty($query['q'])) {
            $sql .= ' AND (title LIKE :q OR tags LIKE :q)';
            $params[':q'] = '%' . $query['q'] . '%';
        }
        if (!empty($query['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $query['status'];
        }
        $sort = $query['sort'] ?? 'created_at DESC';
        $allowed = ['created_at DESC','created_at ASC','due_at ASC','due_at DESC'];
        if (!in_array($sort, $allowed, true)) $sort = 'created_at DESC';
        $sql .= ' ORDER BY ' . $sort;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function get(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: [];
    }

    public function create(array $data): array
    {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') return ['error' => 'Title required'];
        $tags = trim((string)($data['tags'] ?? ''));
        $status = 'open';
        $due = $data['due_at'] ?? null;
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $stmt = $this->db->prepare('INSERT INTO todos (title,status,tags,due_at,created_at,updated_at) VALUES (:title,:status,:tags,:due,:created,:updated)');
        $stmt->execute([
            ':title' => $title,
            ':status' => $status,
            ':tags' => $tags ?: null,
            ':due' => $due ?: null,
            ':created' => $now,
            ':updated' => $now,
        ]);
        $id = (int)$this->db->lastInsertId();
        return $this->get($id);
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->get($id);
        if (!$existing) return ['error' => 'Not found'];
        $fields = [];
        $params = [':id' => $id];
        foreach (['title','status','tags','due_at'] as $k) {
            if (array_key_exists($k, $data)) {
                $fields[] = "$k = :$k";
                $params[":$k"] = ($data[$k] === '' ? null : $data[$k]);
            }
        }
        if (!$fields) return $existing;
        $fields[] = 'updated_at = :updated';
        $params[':updated'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $sql = 'UPDATE todos SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $this->get($id);
    }

    public function delete(int $id): array
    {
        $stmt = $this->db->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return ['ok' => true];
    }
}
