<?php
declare(strict_types=1);

$isAdminArea = str_starts_with(request_path(), '/admin');
$currentUser = app()->auth()->user();
$imageCount = $imageCount ?? null;
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
        <div class="container header-inner<?= $isAdminArea ? ' admin-header' : '' ?>">
            <div class="brand-wrap">
                <a class="brand" href="<?= e(url()) ?>">Bing bilder</a>
                <div class="brand-subline">Bing Hintergründe<?= $imageCount !== null ? ' · ' . (int) $imageCount . ' Bilder' : '' ?></div>
            </div>

            <nav class="main-nav">
                <?php if ($isAdminArea): ?>
                    <a href="<?= e(url()) ?>">Startseite</a>
                    <a href="<?= e(url('admin')) ?>">Admin</a>
                    <?php if ($currentUser): ?>
                        <form method="post" action="<?= e(url('admin/logout')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-small">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= e(url('admin/login')) ?>">Admin Login</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="nav-counter"><?= $imageCount !== null ? (int) $imageCount . ' Bilder' : 'Galerie' ?></span>
                    <button type="button" class="theme-toggle" id="darkmode-toggle" aria-label="Farbschema umschalten">☀</button>

                    <?php if ($currentUser): ?>
                        <a href="<?= e(url('admin')) ?>">Admin</a>
                        <form method="post" action="<?= e(url('admin/logout')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-small">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= e(url('admin/login')) ?>">Admin Login</a>
                    <?php endif; ?>
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
        const updateIcon = () => {
            button.textContent = root.getAttribute('data-theme') === 'light' ? '☾' : '☀';
        };
        updateIcon();

        button.addEventListener('click', function () {
            const current = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', current);
            localStorage.setItem(key, current);
            updateIcon();
        });
    }
})();
</script>
</body>
</html>