<?php
declare(strict_types=1);

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear = $month === 12 ? $year + 1 : $year;
?>
<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error"><?= e((string) $flashError) ?></div>
<?php endif; ?>

<section class="panel">
    <div class="panel-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Willkommen, <?= e((string) ($user['name'] ?? 'Admin')) ?></p>
        </div>
        <div class="panel-actions">
            <a class="btn" href="/admin/upload">Neues Bild hochladen</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Counter</span>
            <strong class="stat-value"><?= (int) $counterValue ?></strong>
            <span class="stat-note">Nur Anzeige im Admin, Messung im Userbereich.</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Login-Zähler</span>
            <strong class="stat-value"><?= (int) ($user['login'] ?? 0) ?></strong>
            <span class="stat-note">Erhöht sich bei jedem erfolgreichen Login.</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Letzter Login</span>
            <strong class="stat-value">
                <?= !empty($user['lastlogin']) ? e(date('d.m.Y H:i:s', (int) $user['lastlogin'])) : '–' ?>
            </strong>
            <span class="stat-note">Aktualisiert bei jedem Login.</span>
        </div>
    </div>
</section>

<section class="panel">
    <div class="calendar-nav">
        <a class="btn btn-small" href="/admin?monat=<?= $prevMonth ?>&jahr=<?= $prevYear ?>">← Vorheriger Monat</a>
        <h2><?= e(german_month_name($month)) ?> <?= (int) $year ?></h2>
        <a class="btn btn-small" href="/admin?monat=<?= $nextMonth ?>&jahr=<?= $nextYear ?>">Nächster Monat →</a>
    </div>

    <?= $calendar ?>
</section>