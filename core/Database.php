<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(Config $config)
    {
        $host = $config->get('DB_HOST', '127.0.0.1');
        $port = $config->get('DB_PORT', '3306');
        $db = $config->get('DB_NAME', '');
        $charset = $config->get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        try {
            $this->pdo = new PDO(
                $dsn,
                (string) $config->get('DB_USER', ''),
                (string) $config->get('DB_PASS', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Datenbankverbindung fehlgeschlagen.');
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}