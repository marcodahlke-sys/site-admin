<?php
declare(strict_types=1);

create_thumbnail_if_needed((string) $image['name']);
?>
<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error"><?= e((string) $flashError) ?></div>
<?php endif; ?>

<section class="detail-hero">
    <div>
        <div class="detail-date"><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></div>
        <h1><?= e((string) $image['name']) ?></h1>
    </div>
    <div class="panel-actions">
        <a class="btn btn-secondary" href="<?= e(url()) ?>">Zurück</a>
        <a class="btn" href="<?= e(image_url((string) $image['name'])) ?>" target="_blank" rel="noopener">Download</a>
    </div>
</section>

<section class="detail-layout">
    <div class="detail-stage">
        <a href="<?= e(image_url((string) $image['name'])) ?>" target="_blank" rel="noopener">
            <img class="main-image" src="<?= e(image_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
        </a>
    </div>

    <aside class="detail-sidebar">
        <div class="meta-item"><strong>Dateiname:</strong><br><?= e((string) $image['name']) ?></div>
        <div class="meta-item"><strong>Datum:</strong><br><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></div>
        <div class="meta-item"><strong>Zeit:</strong><br><?= e(date('H:i:s', (int) $image['entrytime'])) ?></div>
        <div class="meta-item"><strong>Kategorien:</strong><br><?= e(implode(', ', $imageCategories)) ?></div>

        <?php if (!empty($description['beschreibung'])): ?>
            <div class="meta-item">
                <strong>Beschreibung:</strong><br>
                <?= nl2br(e((string) $description['beschreibung'])) ?>
            </div>
        <?php endif; ?>

        <?php if ($tags !== []): ?>
            <div class="meta-item">
                <strong>Tags:</strong><br>
                <div class="tag-list">
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag-chip"><?= e((string) $tag['tag']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="meta-item">
            <strong>Likes:</strong><br>
            <?= (int) $likes ?>
        </div>

        <form method="post" action="<?= e(url('like')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
            <input type="hidden" name="redirect_to" value="<?= e('bild?id=' . (int) $image['id']) ?>">
            <button type="submit" class="btn like-button"><?= $liked ? 'Like entfernen' : 'Like setzen' ?></button>
        </form>
    </aside>
</section>

<?php if ($related !== []): ?>
<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Ähnliche Bilder</h2>
            <p>Weitere Einträge aus passenden Kategorien.</p>
        </div>
    </div>
    <div class="gallery-grid front-gallery-grid">
        <?php foreach ($related as $entry): ?>
            <?php create_thumbnail_if_needed((string) $entry['name']); ?>
            <a class="gallery-card" href="<?= e(url('bild') . '?id=' . (int) $entry['id']) ?>">
                <img src="<?= e(thumb_url((string) $entry['name'])) ?>" alt="<?= e((string) $entry['name']) ?>">
                <span><?= e(date('d.m.Y', (int) $entry['entrytime'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>