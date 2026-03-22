<?php
declare(strict_types=1);

namespace App\Core;

class App
{
    private static ?self $instance = null;
    private array $container = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function set(string $key, mixed $value): void
    {
        $this->container[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->container[$key] ?? null;
    }

    public function config(): Config
    {
        return $this->get('config');
    }

    public function db(): Database
    {
        return $this->get('db');
    }

    public function session(): Session
    {
        return $this->get('session');
    }

    public function auth(): Auth
    {
        return $this->get('auth');
    }
}