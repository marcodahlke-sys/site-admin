<?php
declare(strict_types=1);

class AuthController extends App\Core\Controller
{
    public function loginForm(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/admin');
        }

        $this->view('admin/login', [
            'pageTitle' => 'Admin Login',
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
        ]);
    }

    public function login(): void
    {
        verify_csrf();

        $username = trim((string) input('username', ''));
        $password = (string) input('password', '');
        $remember = (string) input('remember', '0') === '1';

        with_old([
            'username' => $username,
            'remember' => $remember ? '1' : '0',
        ]);

        if ($username === '' || $password === '') {
            $this->session->flash('error', 'Bitte Benutzername und Passwort eingeben.');
            $this->redirect('/admin/login');
        }

        if (!$this->auth->attempt($username, $password, $remember)) {
            $this->session->flash('error', 'Login fehlgeschlagen.');
            $this->redirect('/admin/login');
        }

        clear_old();
        $this->session->flash('success', 'Erfolgreich eingeloggt.');
        $this->redirect('/admin');
    }

    public function logout(): void
    {
        verify_csrf();
        $this->auth->logout();
        session_start();
        $_SESSION['_flash']['success'] = 'Erfolgreich ausgeloggt.';
        redirect('/admin/login');
    }
}