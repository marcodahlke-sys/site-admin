<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    private Database $db;
    private Config $config;
    private \AdminUser $users;

    public function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->users = new \AdminUser($db);
    }

    public function user(): ?array
    {
        $session = App::getInstance()->session();
        $userId = (int) $session->get('admin_user_id', 0);

        if ($userId <= 0) {
            return null;
        }

        return $this->users->findById($userId);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function attempt(string $username, string $password, bool $remember): bool
    {
        $user = $this->users->findByName($username);

        if (!$user || empty($user['pass'])) {
            return false;
        }

        if (!password_verify($password, (string) $user['pass'])) {
            return false;
        }

        $session = App::getInstance()->session();
        $session->regenerate();
        $session->put('admin_user_id', (int) $user['userid']);

        $this->users->registerSuccessfulLogin((int) $user['userid']);

        if ($remember) {
            $this->setRememberCookie((int) $user['userid']);
        }

        return true;
    }

    public function logout(): void
    {
        $user = $this->user();

        if ($user) {
            $this->users->clearRememberToken((int) $user['userid']);
        }

        $cookieName = (string) $this->config->get('REMEMBER_COOKIE_NAME', 'remember_token');

        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => $this->isSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $session = App::getInstance()->session();
        $session->destroy();
    }

    public function bootRememberLogin(): void
    {
        if ($this->check()) {
            return;
        }

        $cookieName = (string) $this->config->get('REMEMBER_COOKIE_NAME', 'remember_token');
        $cookie = $_COOKIE[$cookieName] ?? '';

        if ($cookie === '' || !str_contains($cookie, ':')) {
            return;
        }

        [$userId, $plainToken] = explode(':', $cookie, 2);
        $userId = (int) $userId;

        if ($userId <= 0 || $plainToken === '') {
            return;
        }

        $user = $this->users->findById($userId);

        if (!$user || empty($user['remember_token']) || empty($user['token_expiry'])) {
            return;
        }

        if ((int) $user['token_expiry'] < time()) {
            $this->users->clearRememberToken($userId);
            return;
        }

        if (!hash_equals((string) $user['remember_token'], hash('sha256', $plainToken))) {
            return;
        }

        $session = App::getInstance()->session();
        $session->regenerate();
        $session->put('admin_user_id', $userId);

        $this->users->registerSuccessfulLogin($userId);
        $this->setRememberCookie($userId);
    }

    private function setRememberCookie(int $userId): void
    {
        $plainToken = bin2hex(random_bytes(32));
        $expiry = time() + (((int) $this->config->get('REMEMBER_DAYS', 30)) * 86400);

        $this->users->setRememberToken($userId, hash('sha256', $plainToken), $expiry);

        $cookieName = (string) $this->config->get('REMEMBER_COOKIE_NAME', 'remember_token');

        setcookie(
            $cookieName,
            $userId . ':' . $plainToken,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => $this->isSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function isSecure(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}