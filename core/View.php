<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewCandidates = [
            BASE_PATH . '/views/' . $view . '.php',
            BASE_PATH . '/Views/' . $view . '.php',
            BASE_PATH . '/views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php',
            BASE_PATH . '/Views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php',
        ];

        $layoutCandidates = [
            BASE_PATH . '/views/layouts/' . $layout . '.php',
            BASE_PATH . '/views/Layouts/' . $layout . '.php',
            BASE_PATH . '/Views/layouts/' . $layout . '.php',
            BASE_PATH . '/Views/Layouts/' . $layout . '.php',
        ];

        $viewFile = self::resolveFile($viewCandidates);
        $layoutFile = self::resolveFile($layoutCandidates);

        if ($viewFile === null || $layoutFile === null) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');

            echo "View-Datei fehlt.\n\n";
            echo "BASE_PATH:\n" . BASE_PATH . "\n\n";
            echo "Gesuchte View:\n" . $view . "\n\n";
            echo "Gesuchtes Layout:\n" . $layout . "\n\n";

            echo "View-Kandidaten:\n";
            foreach ($viewCandidates as $file) {
                echo '- ' . $file . ' [' . (is_file($file) ? 'OK' : 'FEHLT') . "]\n";
            }

            echo "\nLayout-Kandidaten:\n";
            foreach ($layoutCandidates as $file) {
                echo '- ' . $file . ' [' . (is_file($file) ? 'OK' : 'FEHLT') . "]\n";
            }

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