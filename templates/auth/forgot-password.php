<!-- Contenu de la page -->
<div class="container" style="margin-top: 6rem !important;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Réinitialiser le mot de passe</h2>
                </div>
                <div class="card-body">
                    <form id="passwordResetForm" method="POST" action="/password-reset">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data["csrf_token"], ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Champ email -->
                        <div class="form-group">
                            <label for="email">Entrez votre email</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <small id="emailError" class="text-danger"></small>
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

            const email = $('#email').val().trim();
            const emailError = $('#emailError');
            const pswdResetBtn = $('#pswdResetBtn');
            emailError.text(''); // Réinitialiser les messages d'erreur
            pswdResetBtn.prop('disabled', true); // Désactiver le bouton pour éviter le spam

            // Vérification du format de l'email
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(email)) {
                emailError.text('Veuillez entrer une adresse email valide.');
                pswdResetBtn.prop('disabled', false);
                return;
            }

            // Vérification côté serveur avec AJAX
            $.ajax({
                url: '/password-reset',
                type: 'POST',
                data: { email: email, csrf_token: $('input[name="csrf_token"]').val() },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToaster(data.message);
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
