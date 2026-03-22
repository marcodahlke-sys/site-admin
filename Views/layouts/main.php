<?php
declare(strict_types=1);

$isAdminArea = str_starts_with(request_path(), '/admin');
$currentUser = app()->auth()->user();
?>
<!doctype html>
<html lang="de" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Bilder-Webseite') ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
</head>
<body>
<div class="site-shell">
    <header class="site-header">
        <div class="container header-inner">
            <div>
                <a class="brand" href="/">Bilder-Webseite</a>
                <div class="brand-subline">User- und Admin-Bereich</div>
            </div>

            <nav class="main-nav">
                <a href="/">Userbereich</a>
                <?php if ($currentUser): ?>
                    <a href="/admin">Admin</a>
                    <form method="post" action="/admin/logout" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-small">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/admin/login">Admin Login</a>
                <?php endif; ?>

                <?php if (!$isAdminArea): ?>
                    <button type="button" class="btn btn-small" id="darkmode-toggle">Darkmode</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?= $content ?>
        </div>
    </main>
</div>

<script>
(function () {
    const root = document.documentElement;
    const key = 'bilder_theme';
    const saved = localStorage.getItem(key);
    if (saved === 'light' || saved === 'dark') {
        root.setAttribute('data-theme', saved);
    }

    const button = document.getElementById('darkmode-toggle');
    if (button) {
        const updateText = () => {
            button.textContent = root.getAttribute('data-theme') === 'light' ? 'Darkmode' : 'Lightmode';
        };
        updateText();

        button.addEventListener('click', function () {
            const current = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', current);
            localStorage.setItem(key, current);
            updateText();
        });
    }
})();
</script>
</body>
</html>