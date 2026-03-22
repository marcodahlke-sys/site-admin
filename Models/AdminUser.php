<?php
declare(strict_types=1);

class AdminUser extends BaseModel
{
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM zugriff1 WHERE userid = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM zugriff1 WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function registerSuccessfulLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE zugriff1
             SET login = CAST(COALESCE(login, "0") AS UNSIGNED) + 1,
                 lastlogin = :lastlogin
             WHERE userid = :id'
        );
        $stmt->execute([
            'lastlogin' => time(),
            'id' => $userId,
        ]);
    }

    public function setRememberToken(int $userId, string $hashedToken, int $expiry): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE zugriff1 SET remember_token = :token, token_expiry = :expiry WHERE userid = :id'
        );
        $stmt->execute([
            'token' => $hashedToken,
            'expiry' => $expiry,
            'id' => $userId,
        ]);
    }

    public function clearRememberToken(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE zugriff1 SET remember_token = NULL, token_expiry = NULL WHERE userid = :id'
        );
        $stmt->execute(['id' => $userId]);
    }
}