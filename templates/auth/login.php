<?php 
if (!$blocked && $attempts <= 4) : ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Connexion</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <form method="POST" action="/login">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-3">
                                <label for="identifier" class="form-label">Email, Username ou Téléphone</label>
                                <input type="text" name="identifier" id="identifier" class="form-control <?= !empty($error) ? 'is-invalid' : '' ?>" required
                                    value="<?= htmlspecialchars($_POST['identifier'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100" name="submit">Se connecter</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/forgot-password" class="text-muted">Mot de passe oublié ?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Connexion</h2>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger"><?= htmlspecialchars(empty($error) ? "Vous ne pouvez pas se connecter!" : $error, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>