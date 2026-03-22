<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = self::resolveFile([
            BASE_PATH . '/views/' . $view . '.php',
            BASE_PATH . '/Views/' . $view . '.php',
            BASE_PATH . '/views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php',
            BASE_PATH . '/Views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php',
        ]);

        $layoutFile = self::resolveFile([
            BASE_PATH . '/views/layouts/' . $layout . '.php',
            BASE_PATH . '/views/Layouts/' . $layout . '.php',
            BASE_PATH . '/Views/layouts/' . $layout . '.php',
            BASE_PATH . '/Views/Layouts/' . $layout . '.php',
        ]);

        if ($viewFile === null || $layoutFile === null) {
            http_response_code(500);
            echo 'View-Datei fehlt.';
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    private static function resolveFile(array $candidates): ?string
    {
        foreach ($candidates as $file) {
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }
}