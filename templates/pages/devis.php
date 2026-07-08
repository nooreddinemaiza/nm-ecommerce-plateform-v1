<style>
    .col-lg-12 {
        margin: 12px 0;
    }

    .removeProduct {
        margin: 10px auto;
    }
</style>
<div class="page-heading header-text" style="background-color:rgb(0, 89, 255);">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Demande de devis</h3>
                <span class="breadcrumb"><a href="/">Acceuil</a> > Devis </span>
            </div>
        </div>
    </div>
</div>
<div class="contact-page section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 align-self-center">
                <div class="left-text">
                    <div class="section-heading">
                        <h6>Demande de devis</h6>
                        <h2>
                            Votre satisfaction est notre priorité
                        </h2>
                    </div>
                    <p>
                        Obtenez une offre sur mesure adaptée à vos besoins spécifiques. Notre équipe vous préparera un devis détaillé sous 24 heures.
                    </p>
                </div>
            </div>
            <div class="col-lg-6" style="padding: 10px;background: #ebebeb;border-radius: 25px;padding-top: 35px;">
                <div class="right-content">
                    <div class="row">
                        <div class="col-lg-12" id="devis-area">
                            <form id="devisForm">
                                <div class="row">
                                    <input class="form-control" type="hidden" id="csrf_token" name="csrf_token" value="<?= $csrf_token ?>">
                                    <div class="col-lg-12 form-group">
                                        <label>Nom Complet *</label>
                                        <small style="margin-left: 20px;" class="error-message" id="name-error"></small>
                                        <input class="form-control" class="form-control" type="text" name="nom" id="nom" placeholder="Votre Nom Complet">
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label>E-mail professionnel *</label>
                                        <small style="margin-left: 20px;" class="error-message" id="email-error"></small>
                                        <input class="form-control" type="text" name="email" id="email" placeholder="Votre E-mail professionnel...">
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label>Téléphone</label>
                                        <small style="margin-left: 20px;" class="error-message" id="subject-error"></small>
                                        <input class="form-control" type="tel" name="telephone" id="telephone" placeholder="Votre Téléphone">
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label>Produits souhaités</label>
                                        <div id="produitsContainer">
                                            <div class="produit-select">
                                                <select class="form-control" name="produits[]">
                                                    <option value="">Sélectionner un produit</option>
                                                    <?php foreach ($products as $produit) { ?>
                                                        <option value="<?= htmlspecialchars($produit['id']) ?>"><?= htmlspecialchars($produit['title']) ?></option>
                                                    <?php } ?>
                                                </select>
                                                <button type="button" class="removeProduct" style="display:none;">Supprimer</button>
                                            </div>
                                        </div>
                                        <button type="button" id="ajouterProduit" class="btn-secondary">+ Ajouter un autre produit</button>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label for="notInListeDet">Pas dans la liste?</label>
                                        <small class="form-text text-muted">
                                            Si votre produit ne figure pas dans la liste, utilisez cette section pour décrire vos besoins spécifiques.
                                        </small>
                                        <textarea id="notInListdescr" name="notInListdescr"
                                            placeholder="Décrivez votre produit"></textarea>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label for="details">Détails supplémentaires</label>
                                        <textarea id="details" name="details" rows="5" placeholder="Précisez vos besoins spécifiques, délais souhaités, ou toute autre information utile..."></textarea>
                                    </div>
                                    <div class="col-lg-12" id="captchaContainer">
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <button type="submit" id="form-submit" class="orange-button">Demander mon devis</button>
                                    </div>
                                </div>
                                <script>
                                    const produits = <?= json_encode($products) ?>;
                                    $(document).ready(function() {
                                        $('#page-header').css('top', '-20px')
                                            .css('left', '5%')
                                            .css('position', 'fixed');
                                        $(window).scroll(function() {
                                            if (($(this).scrollTop() < 20)) {
                                                $('#page-header').css('top', '0');
                                            } else {
                                                $('#page-header').css('top', '-20px');
                                            }
                                        });
                                    });
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>