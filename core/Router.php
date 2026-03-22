<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = request_path();
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo 'Seite nicht gefunden.';
            return;
        }

        [$class, $action] = $handler;

        if (!class_exists($class)) {
            http_response_code(500);
            echo 'Controller nicht gefunden.';
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo 'Action nicht gefunden.';
            return;
        }

        $controller->{$action}();
    }
}