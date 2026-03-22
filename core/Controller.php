<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected App $app;
    protected Database $db;
    protected Config $config;
    protected Session $session;
    protected Auth $auth;

    public function __construct()
    {
        $this->app = App::getInstance();
        $this->db = $this->app->db();
        $this->config = $this->app->config();
        $this->session = $this->app->session();
        $this->auth = $this->app->auth();
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function requireAdmin(): void
    {
        if (!$this->auth->check()) {
            $this->session->flash('error', 'Bitte zuerst einloggen.');
            $this->redirect('admin/login');
        }
    }
}