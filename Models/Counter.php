<?php
declare(strict_types=1);

class Counter extends BaseModel
{
    public function getCount(): int
    {
        $stmt = $this->pdo->query('SELECT count FROM counter ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return (int) ($row['count'] ?? 0);
    }

    public function increment(): void
    {
        $stmt = $this->pdo->query('SELECT id, count FROM counter ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();

        if ($row) {
            $update = $this->pdo->prepare('UPDATE counter SET count = :count WHERE id <=> :id');
            $update->bindValue('count', ((int) $row['count']) + 1, PDO::PARAM_INT);

            if ($row['id'] === null) {
                $update->bindValue('id', null, PDO::PARAM_NULL);
            } else {
                $update->bindValue('id', (int) $row['id'], PDO::PARAM_INT);
            }

            $update->execute();
            return;
        }

        $insert = $this->pdo->prepare('INSERT INTO counter (id, count) VALUES (1, 1)');
        $insert->execute();
    }
}