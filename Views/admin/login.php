<?php
declare(strict_types=1);
?>
<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error"><?= e((string) $flashError) ?></div>
<?php endif; ?>

<section class="panel narrow-panel">
    <div class="panel-header">
        <div>
            <h1>Admin Login</h1>
            <p>Login über zugriff1.name und Passwortprüfung via password_verify().</p>
        </div>
    </div>

    <form method="post" action="/admin/login" class="form-grid">
        <?= csrf_field() ?>

        <div class="field">
            <label for="username">Benutzername</label>
            <input id="username" type="text" name="username" maxlength="20" value="<?= e((string) old('username')) ?>" required>
        </div>

        <div class="field">
            <label for="password">Passwort</label>
            <input id="password" type="password" name="password" required>
        </div>

        <label class="checkbox">
            <input type="checkbox" name="remember" value="1" <?= old('remember') === '1' ? 'checked' : '' ?>>
            Eingeloggt bleiben
        </label>

        <div class="form-actions">
            <button type="submit" class="btn">Einloggen</button>
        </div>
    </form>
</section>