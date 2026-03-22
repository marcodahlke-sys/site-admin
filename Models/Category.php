<?php
declare(strict_types=1);

class Category extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM vup_kategorien ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function indexed(): array
    {
        $result = [];
        foreach ($this->all() as $row) {
            $result[(int) $row['id']] = $row['name'];
        }
        return $result;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM vup_kategorien WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}