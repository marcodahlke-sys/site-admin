<?php
declare(strict_types=1);

class Tag extends BaseModel
{
    public function forImage(int $imageId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, tag, aktiv, html FROM tags WHERE bid = :id ORDER BY id ASC'
        );
        $stmt->execute(['id' => $imageId]);
        return $stmt->fetchAll();
    }

    public function replaceForImage(int $imageId, array $tags): void
    {
        $delete = $this->pdo->prepare('DELETE FROM tags WHERE bid = :id');
        $delete->execute(['id' => $imageId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO tags (bid, tag, aktiv, html) VALUES (:bid, :tag, "1", "0")'
        );

        foreach ($tags as $tag) {
            $tag = trim((string) $tag);
            if ($tag === '') {
                continue;
            }

            $insert->execute([
                'bid' => $imageId,
                'tag' => mb_substr($tag, 0, 255),
            ]);
        }
    }
}