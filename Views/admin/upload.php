<?php
declare(strict_types=1);

$extraSelected = old('extra_categories', []);
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
            <h1>Bild hochladen</h1>
            <p>Nur JPG, Dateiname automatisch als unix-timestamp.jpg, maximal ein neues Bild pro Tag.</p>
        </div>
    </div>

    <form method="post" action="<?= e(url('admin/upload')) ?>" enctype="multipart/form-data" class="form-grid">
        <?= csrf_field() ?>

        <div class="field">
            <label for="date">Datum</label>
            <input id="date" type="date" name="date" value="<?= e((string) old('date', $defaultDate)) ?>" required>
        </div>

        <div class="field">
            <label for="time">Uhrzeit</label>
            <input id="time" type="time" name="time" value="<?= e((string) old('time', '12:00')) ?>" required>
        </div>

        <div class="field">
            <label for="to_kat">Hauptkategorie</label>
            <select id="to_kat" name="to_kat" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) old('to_kat') === (int) $category['id'] ? 'selected' : '' ?>>
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
                        <input type="checkbox" name="extra_categories[<?= $catId ?>]" value="1" <?= (($extraSelected[$catId] ?? '') === '1') ? 'checked' : '' ?>>
                        <?= e((string) $category['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="field field-full">
            <label for="beschreibung">Beschreibung</label>
            <textarea id="beschreibung" name="beschreibung" rows="5"><?= e((string) old('beschreibung')) ?></textarea>
        </div>

        <div class="field field-full">
            <label for="tags">Tags</label>
            <input id="tags" type="text" name="tags" value="<?= e((string) old('tags')) ?>" placeholder="z. B. natur, wald, sonnenuntergang">
        </div>

        <div class="field field-full">
            <label for="image">JPG-Datei</label>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,image/jpeg" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Hochladen</button>
            <a class="btn btn-secondary" href="<?= e(url('admin')) ?>">Abbrechen</a>
        </div>
    </form>
</section>