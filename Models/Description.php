<?php
declare(strict_types=1);

class Description extends BaseModel
{
    public function getByImageId(int $imageId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, beschreibung, ok FROM beschreibung1 WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $imageId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(int $imageId, string $description): void
    {
        $exists = $this->getByImageId($imageId);

        if ($exists) {
            $stmt = $this->pdo->prepare(
                'UPDATE beschreibung1 SET beschreibung = :beschreibung, ok = "1" WHERE id = :id'
            );
            $stmt->execute([
                'beschreibung' => $description,
                'id' => $imageId,
            ]);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO beschreibung1 (id, beschreibung, ok) VALUES (:id, :beschreibung, "1")'
        );
        $stmt->execute([
            'id' => $imageId,
            'beschreibung' => $description,
        ]);
    }

    public function deleteByImageId(int $imageId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM beschreibung1 WHERE id = :id');
        $stmt->execute(['id' => $imageId]);
    }
}