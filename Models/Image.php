<?php
declare(strict_types=1);

class Image extends BaseModel
{
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vup_dateien WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $image = $stmt->fetch();
        return $image ?: null;
    }

    public function getByMonth(int $month, int $year, ?int $categoryId = null): array
    {
        $start = mktime(0, 0, 0, $month, 1, $year);
        $end = mktime(23, 59, 59, $month, (int) date('t', $start), $year);

        $sql = 'SELECT * FROM vup_dateien WHERE entrytime >= :start AND entrytime <= :end';
        $params = [
            'start' => $start,
            'end' => $end,
        ];

        if ($categoryId !== null) {
            $conditions = ['to_kat = :categoryId'];
            $params['categoryId'] = $categoryId;

            $map = category_field_map();
            if (isset($map[$categoryId])) {
                $conditions[] = $map[$categoryId] . ' = "1"';
            }

            $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
        }

        $sql .= ' ORDER BY entrytime ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function latest(int $limit = 24, ?int $categoryId = null): array
    {
        $sql = 'SELECT * FROM vup_dateien';
        $params = [];

        if ($categoryId !== null) {
            $conditions = ['to_kat = :categoryId'];
            $params['categoryId'] = $categoryId;

            $map = category_field_map();
            if (isset($map[$categoryId])) {
                $conditions[] = $map[$categoryId] . ' = "1"';
            }

            $sql .= ' WHERE (' . implode(' OR ', $conditions) . ')';
        }

        $sql .= ' ORDER BY entrytime DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function createFromUpload(array $file, int $entrytime, int $mainCategory, array $extraCategories): int
    {
        if (!is_valid_jpg_upload($file)) {
            throw new RuntimeException('Bitte eine gültige JPG-Datei hochladen.');
        }

        $dayStart = strtotime(date('Y-m-d 00:00:00', $entrytime));
        $dayEnd = strtotime(date('Y-m-d 23:59:59', $entrytime));

        $check = $this->pdo->prepare(
            'SELECT id FROM vup_dateien WHERE entrytime >= :start AND entrytime <= :end LIMIT 1'
        );
        $check->execute([
            'start' => $dayStart,
            'end' => $dayEnd,
        ]);

        if ($check->fetch()) {
            throw new RuntimeException('Es ist nur ein neues Bild pro Tag erlaubt.');
        }

        ensure_directory(uploads_path());
        ensure_directory(thumbs_path());

        $filename = $entrytime . '.jpg';
        $destination = uploads_path($filename);

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Datei konnte nicht gespeichert werden.');
        }

        $realSize = (string) filesize($destination);

        $fields = [
            'entrytime' => (string) $entrytime,
            'name' => $filename,
            'size' => $realSize,
            'ordner' => '',
            'to_kat' => $mainCategory,
            'aktivator' => 1,
            'video' => '0',
            'k1' => '0',
            'k3' => '0',
            'k4' => '0',
            'k7' => '0',
            'k9' => '0',
            'k10' => '0',
            'k13' => '0',
            'k15' => '0',
            'empty' => '0',
            'downloads' => 0,
        ];

        foreach (category_field_map() as $catId => $field) {
            if (in_array($catId, $extraCategories, true)) {
                $fields[$field] = '1';
            }
        }

        $sql = 'INSERT INTO vup_dateien
            (entrytime, name, size, ordner, to_kat, aktivator, video, k1, k3, k4, k7, k9, k10, k13, k15, empty, downloads)
            VALUES
            (:entrytime, :name, :size, :ordner, :to_kat, :aktivator, :video, :k1, :k3, :k4, :k7, :k9, :k10, :k13, :k15, :empty, :downloads)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($fields);

        create_thumbnail_if_needed($filename);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateImage(int $id, int $entrytime, int $mainCategory, array $extraCategories): void
    {
        $image = $this->getById($id);

        if (!$image) {
            throw new RuntimeException('Bild nicht gefunden.');
        }

        $oldFilename = (string) $image['name'];
        $newFilename = $entrytime . '.jpg';

        $dayStart = strtotime(date('Y-m-d 00:00:00', $entrytime));
        $dayEnd = strtotime(date('Y-m-d 23:59:59', $entrytime));

        $check = $this->pdo->prepare(
            'SELECT id FROM vup_dateien
             WHERE entrytime >= :start AND entrytime <= :end AND id != :id LIMIT 1'
        );
        $check->execute([
            'start' => $dayStart,
            'end' => $dayEnd,
            'id' => $id,
        ]);

        if ($check->fetch()) {
            throw new RuntimeException('Für dieses Datum existiert bereits ein anderes Bild.');
        }

        if ($newFilename !== $oldFilename) {
            $oldPath = uploads_path($oldFilename);
            $newPath = uploads_path($newFilename);

            if (!is_file($oldPath)) {
                throw new RuntimeException('Originaldatei fehlt.');
            }

            if (!rename($oldPath, $newPath)) {
                throw new RuntimeException('Dateiname konnte nicht geändert werden.');
            }

            $oldThumb = thumbs_path($oldFilename);
            $newThumb = thumbs_path($newFilename);

            if (is_file($oldThumb)) {
                @rename($oldThumb, $newThumb);
            }
        }

        $fields = [
            'entrytime' => (string) $entrytime,
            'name' => $newFilename,
            'size' => (string) filesize(uploads_path($newFilename)),
            'to_kat' => $mainCategory,
            'id' => $id,
            'k1' => '0',
            'k3' => '0',
            'k4' => '0',
            'k7' => '0',
            'k9' => '0',
            'k10' => '0',
            'k13' => '0',
            'k15' => '0',
        ];

        foreach (category_field_map() as $catId => $field) {
            if (in_array($catId, $extraCategories, true)) {
                $fields[$field] = '1';
            }
        }

        $stmt = $this->pdo->prepare(
            'UPDATE vup_dateien SET
                entrytime = :entrytime,
                name = :name,
                size = :size,
                to_kat = :to_kat,
                k1 = :k1,
                k3 = :k3,
                k4 = :k4,
                k7 = :k7,
                k9 = :k9,
                k10 = :k10,
                k13 = :k13,
                k15 = :k15,
                video = "0",
                empty = "0"
             WHERE id = :id'
        );
        $stmt->execute($fields);

        create_thumbnail_if_needed($newFilename);
    }

    public function deleteImage(int $id): void
    {
        $image = $this->getById($id);

        if (!$image) {
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM vup_dateien WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $file = uploads_path((string) $image['name']);
        $thumb = thumbs_path((string) $image['name']);

        if (is_file($file)) {
            @unlink($file);
        }

        if (is_file($thumb)) {
            @unlink($thumb);
        }
    }

    public function categoriesForImage(array $image): array
    {
        $result = [(int) $image['to_kat']];

        foreach (category_field_map() as $catId => $field) {
            if (($image[$field] ?? '0') === '1') {
                $result[] = $catId;
            }
        }

        return array_values(array_unique($result));
    }
}