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
            <h1>Userbereich</h1>
            <p>Kalenderansicht, Kategorien, Bilder, Tags, Beschreibung und Likes.</p>
        </div>
    </div>

    <form method="get" action="/" class="filter-bar">
        <div class="field">
            <label for="kat">Kategorie</label>
            <select id="kat" name="kat">
                <option value="">Alle Kategorien</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="monat">Monat</label>
            <select id="monat" name="monat">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= e(german_month_name($m)) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="field">
            <label for="jahr">Jahr</label>
            <input id="jahr" type="number" name="jahr" value="<?= (int) $year ?>" min="1970" max="2100">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn">Filtern</button>
            <a class="btn btn-secondary" href="/">Zurücksetzen</a>
        </div>
    </form>
</section>

<section class="panel">
    <div class="calendar-nav">
        <a class="btn btn-small" href="/<?= ltrim(preserve_query(['monat' => $prevMonth, 'jahr' => $prevYear]), '?') ?>">← Vorheriger Monat</a>
        <h2><?= e(german_month_name($month)) ?> <?= (int) $year ?></h2>
        <a class="btn btn-small" href="/<?= ltrim(preserve_query(['monat' => $nextMonth, 'jahr' => $nextYear]), '?') ?>">Nächster Monat →</a>
    </div>

    <?= $calendar ?>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Neueste Bilder</h2>
            <p>Schnellzugriff auf die letzten Einträge.</p>
        </div>
    </div>

    <div class="gallery-grid">
        <?php foreach ($latest as $image): ?>
            <?php create_thumbnail_if_needed((string) $image['name']); ?>
            <a class="gallery-card" href="/bild?id=<?= (int) $image['id'] ?>">
                <img src="<?= e(thumb_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
                <span><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>