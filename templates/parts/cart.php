        <!-- Panier -->
        <div id="panier-container" class="container position-fixed top-0 end-0 p-3" style="display: none;max-width: 590px; margin-top: 80px; margin-right: 2%; z-index: 2000;">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Mon Panier</h5>
                    <i id="panier-close" class="fa-solid fa-times fs-4" style="cursor: pointer;"></i>
                </div>
                <div class="card-body p-3" style="max-height: 300px; overflow-y: auto; ">
                    <div id="panier-content">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix</th>
                                    <th>Qté</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="panier-items">
                                <!-- Contenu ajouté dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                    <div id="panier-empty" class="text-center text-muted py-5 d-none">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <h5>Votre panier est vide</h5>
                        <p>Ajoutez des articles pour commencer vos achats.</p>
                        <a href="/shop" class="btn btn-outline-primary mt-2" id="continuer-shopping">
                            <i class="fas fa-store me-2"></i> Découvrir nos produits
                        </a>
                        <a href="/consulter-commande" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i>Consulter une commande
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center d-none" id="commande-buttons">
                    <button class="btn btn-outline-secondary me-2" id="continuer-shopping">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </button>
                    <a href="/checkout" class="btn btn-success" id="valider-commande">
                        <i class="fas fa-check-circle me-2"></i>Finaliser
                    </a>
                </div>
            </div>
        </div>
        <div id='custom-toast' data-confirm="delete" data-index="" data-id=""
            class="custom-toast "
            style="z-index: 2099 !important; height: fit-content; display: none;">
            <div class="toast-icon">
                <i class="fas fa-exclamation-triangle pulse"></i>
            </div>
            <p class="toast-message">Confirmez la suppression</p>
            <div class="toast-actions">
                <button id="toast-confirm" class="btn-toast confirm">
                    <i class="fas fa-check"></i> Confirmer
                </button>
                <button id="toast-cancel" class="btn-toast cancel" onclick="$('#custom-toast').fadeOut()">
                    <i class="fas fa-times"></i> Annuler
                </button>
            </div>
        </div>