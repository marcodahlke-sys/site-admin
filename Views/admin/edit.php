<?php
declare(strict_types=1);
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
            <h1>Bild bearbeiten</h1>
            <p><?= e((string) $image['name']) ?></p>
        </div>
    </div>

    <div class="edit-preview">
        <img src="<?= e(thumb_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
    </div>

    <form method="post" action="<?= e(url('admin/edit')) ?>" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $image['id'] ?>">

        <div class="field">
            <label for="date">Datum</label>
            <input id="date" type="date" name="date" value="<?= e($dateValue) ?>" required>
        </div>

        <div class="field">
            <label for="time">Uhrzeit</label>
            <input id="time" type="time" name="time" value="<?= e($timeValue) ?>" required>
        </div>

        <div class="field">
            <label for="to_kat">Hauptkategorie</label>
            <select id="to_kat" name="to_kat" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) $image['to_kat'] === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field field-full">
            <label>Zusätzliche Kategorien</label>
            <div class="checkbox-grid">
                <?php foreach ($categories as $category): ?>
                    <?php $catId = (int) $category['id']; ?>
                    <?php if (!array_key_exists($catId, category_field_map())) continue; ?>
                    <label class="checkbox">
                        <input type="checkbox" name="extra_categories[<?= $catId ?>]" value="1" <?= in_array($catId, $selectedCategories, true) && $catId !== (int) $image['to_kat'] ? 'checked' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="field field-full">
            <label for="beschreibung">Beschreibung</label>
            <textarea id="beschreibung" name="beschreibung" rows="5"><?= e($descriptionText) ?></textarea>
        </div>

        <div class="field field-full">
            <label for="tags">Tags</label>
            <input id="tags" type="text" name="tags" value="<?= e($tagsString) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Speichern</button>
            <a class="btn btn-secondary" href="<?= e(url('admin')) ?>">Zurück</a>
        </div>
    </form>
</section>