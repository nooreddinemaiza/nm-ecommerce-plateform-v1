$(document).ready(function () {
    // Fonction pour ajouter un produit supplémentaire
    $('#ajouterProduit').on('click', function () {
        const $container = $('#produitsContainer');
        const $newSelect = $('<div>').addClass('produit-select mb-2');

        // Création des options
        let optionsHtml = '<option value="">Sélectionner un produit (optionnel)</option>';
        produits.forEach(produit => {
            optionsHtml += `<option value="${produit.id}">${escapeHtml(produit.title)}</option>`;
        });

        // Construction du nouveau champ
        $newSelect.html(`
            <select name="produits[]" class="form-select">
                ${optionsHtml}
            </select>
            <button type="button" class="btn btn-danger btn-sm removeProduct ms-2">
                <i class="fas fa-times"></i> Supprimer
            </button>
        `);

        // Ajout au container
        $container.append($newSelect);
    });

    // Gestion de la suppression des champs produits
    $(document).on('click', '.removeProduct', function () {
        $(this).parent().remove();
    });

    // Validation et envoi du formulaire
    $('#devisForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();

        // Animation de chargement
        $submitBtn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Envoi en cours...
        `);

        // Validation des champs obligatoires
        const nom = $('#nom').val().trim();
        const email = $('#email').val().trim();
        const notInListdescr = $('#notInListdescr').val().trim();
        let hasProducts = false;

        // Vérification des champs obligatoires
        if (!nom || !email) {
            showError('Veuillez remplir tous les champs obligatoires (nom et email).');
            resetButton($submitBtn, originalBtnText);
            return;
        }

        // Vérification des produits
        $('select[name="produits[]"]').each(function () {
            if ($(this).val()) hasProducts = true;
        });

        if (!hasProducts && !notInListdescr) {
            showError('Veuillez sélectionner au moins un produit ou décrire votre besoin.');
            resetButton($submitBtn, originalBtnText);
            return;
        }
        $.ajax({
            type: "POST",
            url: "/devis/get-captcha",
            success: function (response) {
                response = JSON.parse(response)
                if (response.success) {
                    $("#captchaContainer").html(response.data);
                    $("#form-submit").remove();
                }
            },
            error: function (xhr, status, error) {
                // Gestion des erreurs
                showToaster("Une erreur s'est produite lors de l'envoi du message. Veuillez réessayer.");
                console.error(xhr, status, error);
            }
        });
    });

    // Fonctions utilitaires
    function showError(message) {
        showToaster(message);
        // Ajout d'une alerte Bootstrap
        if ($('#formErrorAlert').length === 0) {
            $('#devisForm').prepend(`
                <div id="formErrorAlert" class="alert alert-danger alert-dismissible fade show">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        } else {
            $('#formErrorAlert').html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `).removeClass('d-none');
        }
    }

    function resetButton($btn, originalHtml) {
        $btn.prop('disabled', false).html(originalHtml);
    }

    function animateError($btn, originalHtml) {
        $btn.removeClass('btn-primary').addClass('btn-danger')
            .html('<i class="fas fa-exclamation-circle"></i> Erreur');

        setTimeout(() => {
            $btn.removeClass('btn-danger').addClass('btn-primary')
                .html(originalHtml);
        }, 2000);
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});