<!-- Contenu de la page -->
<div class="container" style="margin-top: 6rem !important;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Réinitialiser le mot de passe</h2>
                </div>
                <div class="card-body">
                    <form id="passwordResetForm" method="POST" action="/set-new-password">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data["csrf_token"], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($data["token"], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="password">Entrez votre nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" required value="<?= htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <small id="passwordError" class="text-danger"></small>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmez votre nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required value="<?= htmlspecialchars($_POST['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <small id="confirm_passwordError" class="text-danger"></small>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Bouton de réinitialisation -->
                        <div class="form-group" style="margin-top: 1rem;">
                            <div class="input-group">
                                <button type="submit" class="btn btn-primary btn-block" id="pswdResetBtn">Réinitialiser</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="/login" class="text-muted">Se connecter</a><br>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#passwordResetForm').submit(function(event) {
            event.preventDefault(); // Empêcher l'envoi immédiat

            const password = $('#password').val().trim();
            const confirm_password = $('#confirm_password').val().trim();
            const passwordError = $('#passwordError');
            const confirm_passwordError = $('#confirm_passwordError');
            const pswdResetBtn = $('#pswdResetBtn');
            passwordError.text(''); // Réinitialiser les messages d'erreur
            // pswdResetBtn.prop('disabled', true); // Désactiver le bouton pour éviter le spam

            // Vérification du format de l'email
            if (password.length < 6) {
                passwordError.text('Le mot de passe doit contenir au moins 6 caractères.');
                pswdResetBtn.prop('disabled', false);
                return;
            }
            if (password !== confirm_password) {
                confirm_passwordError.text('Les mots de passe ne correspondent pas.');
                pswdResetBtn.prop('disabled', false);
                return;
            }
            // Vérification côté serveur avec AJAX
            $.ajax({
                url: '/set-new-password',
                type: 'POST',
                data: {
                    password: password,
                    confirm_password: confirm_password,
                    csrf_token: $('input[name="csrf_token"]').val(),
                    token: $('input[name="token"]').val()
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToaster(data.message);
                        setTimeout(function() {
                            window.location.href = '/login';
                        }, 3000);
                        $('#passwordResetForm')[0].reset(); // Réinitialiser le formulaire
                    } else {
                        showToaster(data.message);
                    }
                },
                error: function() {
                    showToaster('Erreur lors de l’envoi, veuillez réessayer.');
                },
                complete: function() {
                    pswdResetBtn.prop('disabled', false); // Réactiver le bouton après l'envoi
                }
            });
        });
    });
</script>