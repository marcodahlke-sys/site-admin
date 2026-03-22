<?php
declare(strict_types=1);

class Like extends BaseModel
{
    public function countByImage(int $imageId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS cnt FROM likes WHERE datei_id = :id');
        $stmt->execute(['id' => $imageId]);
        $row = $stmt->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    public function existsForImageAndIp(int $imageId, string $ip): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM likes WHERE datei_id = :id AND user_ip = :ip LIMIT 1'
        );
        $stmt->execute([
            'id' => $imageId,
            'ip' => $ip,
        ]);
        return (bool) $stmt->fetch();
    }

    public function add(int $imageId, string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO likes (datei_id, user_ip, datum) VALUES (:id, :ip, :datum)'
        );
        $stmt->execute([
            'id' => $imageId,
            'ip' => $ip,
            'datum' => time(),
        ]);
    }

    public function remove(int $imageId, string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM likes WHERE datei_id = :id AND user_ip = :ip'
        );
        $stmt->execute([
            'id' => $imageId,
            'ip' => $ip,
        ]);
    }
}