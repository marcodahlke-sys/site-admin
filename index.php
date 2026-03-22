<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/core/bootstrap.php';

$router = new App\Core\Router();

$router->get('/', ['HomeController', 'index']);
$router->get('/bild', ['HomeController', 'show']);
$router->post('/like', ['LikeController', 'toggle']);

$router->get('/admin/login', ['AuthController', 'loginForm']);
$router->post('/admin/login', ['AuthController', 'login']);
$router->post('/admin/logout', ['AuthController', 'logout']);

$router->get('/admin', ['AdminController', 'dashboard']);
$router->get('/admin/upload', ['AdminController', 'uploadForm']);
$router->post('/admin/upload', ['AdminController', 'upload']);
$router->get('/admin/edit', ['AdminController', 'editForm']);
$router->post('/admin/edit', ['AdminController', 'update']);
$router->post('/admin/delete', ['AdminController', 'delete']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');