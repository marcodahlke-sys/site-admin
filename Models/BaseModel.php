<?php
declare(strict_types=1);

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct(App\Core\Database $db)
    {
        $this->pdo = $db->pdo();
    }
}