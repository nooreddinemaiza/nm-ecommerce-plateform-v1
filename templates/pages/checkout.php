<?php
// Simuler des produits (remplace ça par les produits de ton panier)
$products = $data['items'];
$siteName = $data['website'];
$realTotal = array_reduce($products, function ($sum, $product) {
    return $sum + ($product['price'] * $product['quantity']);
}, 0);
$totalBeforeDiscount = $data['total'];
$totalDiscount = $realTotal - $totalBeforeDiscount;
$totalAfterDiscount = $totalBeforeDiscount;
?>

<div class="container">
    <style>
        .quantity-info {
            width: fit-content;
            float: right;
        }
    </style>
    <!-- Logo et titre -->
    <div class="text-center mb-4">
        <h1 class="mb-0 animate__animated animate__fadeInDown">
            <i class="fas fa-store me-2 text-primary"></i>
            <a href="/"><?= htmlspecialchars($siteName) ?></a>
        </h1>
        <p class="text-muted animate__animated animate__fadeIn">Finalisez votre commande en quelques étapes</p>
    </div>

    <!-- Indicateur de progression - modifié pour 2 étapes -->
    <div class="progress progress-checkout animate__animated animate__fadeIn">
        <div class="progress-bar bg-success" role="progressbar" style="width: 50%"></div>
    </div>

    <!-- Étapes - modifié pour 2 étapes -->
    <div class="steps-indicator animate__animated animate__fadeIn">
        <div class="step active">
            <div class="step-number">1</div>
            <div class="step-title">Vérifier la commande</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-title">Informations</div>
        </div>
    </div>

    <!-- Étape 1: Liste des produits -->
    <div id="products-section" class="form-step active animate__animated animate__fadeIn">
        <div class="card shadow">
            <div class="card-header card-primary-header">
                <i class="fas fa-shopping-cart me-2"></i> Votre commande
            </div>
            <div class="card-body" style="overflow-x: auto;">
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
            <div class="card-footer">
                <div class="mt-3 text-end">
                    <button id="to-shipping" class="btn btn-custom">
                        Continuer <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 2: Informations du client -->
    <div id="shipping-section" class="form-step">
        <div class="card shadow">
            <div class="card-header card-success-header">
                <i class="fas fa-user me-2"></i> Vos informations
            </div>
            <div class="card-body">
                <form id="shipping-form" class="needs-validation" novalidate>
                    <div class="row">
                        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                        <div class="col-md-6 mb-3">
                            <div class="floating-label">
                                <input type="text" name="first_name" class="form-control" id="first_name" placeholder=" " required>
                                <label for="first_name"><i class="fas fa-user me-2"></i> Prénom</label>
                                <div class="invalid-feedback" id='first_name-feedback'>Veuillez entrer votre prénom.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="floating-label">
                                <input type="text" name="last_name" class="form-control" id="last_name" placeholder=" " required>
                                <label for="last_name"><i class="fas fa-user me-2"></i> Nom</label>
                                <div class="invalid-feedback" id='last_name-feedback'>Veuillez entrer votre nom.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="floating-label">
                            <input type="email" name="email" class="form-control" id="email" placeholder=" " required>
                            <label for="email"><i class="fas fa-envelope me-2"></i> Email</label>
                            <div class="invalid-feedback" id='email-feedback'>Veuillez entrer une adresse email valide.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="floating-label">
                            <input type="tel" name="phone" class="form-control" id="phone" placeholder=" " required>
                            <label for="phone"><i class="fas fa-phone-alt me-2"></i> Téléphone</label>
                            <div class="invalid-feedback" id='phone-feedback'>Veuillez entrer un numéro de téléphone valide.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="floating-label">
                            <input type="text" name="address" class="form-control" id="address" placeholder=" " required>
                            <label for="address"><i class="fas fa-map-marker-alt me-2"></i> Adresse</label>
                            <div class="invalid-feedback" id='address-feedback'>Veuillez entrer votre adresse.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="floating-label">
                                <input type="text" name="postal_code" class="form-control" id="postal_code" placeholder=" ">
                                <label for="postal_code"><i class="fas fa-map me-2"></i> Code postal (optionnel)</label>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <div class="floating-label">
                                <input type="text" name="city" class="form-control" id="city" placeholder=" ">
                                <label for="city"><i class="fas fa-city me-2"></i> Ville (optionnel)</label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i> En validant votre commande, celle-ci sera enregistrée et un membre de notre équipe vous contactera prochainement pour confirmation.
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="back-to-products" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Retour
                        </button>
                        <button type="submit" id="to-summary" class="btn btn-custom">
                            Finaliser la commande <i class="fas fa-check ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Récapitulatif avant confirmation -->
    <div id="summary-section" class="animate__animated animate__fadeIn">
        <div class="card shadow">
            <div class="card-header card-success-header">
                <i class="fas fa-check-circle me-2"></i> Récapitulatif de votre commande
            </div>
            <div class="card-body">
                <div class="summary-products mb-4">
                    <h5 class="mb-3"><i class="fas fa-shopping-bag me-2"></i> Articles</h5>
                    <div class="products-summary">
                        <?php foreach ($products as $product): ?>
                            <div class="summary-item">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/images/product-image/<?= htmlspecialchars($product['image']) ?>" class="product-img me-3" style="width: 50px; height: 50px;" alt="<?= htmlspecialchars($product['title']) ?>">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($product['title']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($product['category']) ?></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between">
                                        <?php
                                        $reduction = ($product['quantity'] >= $product['appReduction']  && $product['appReduction'] != 0) ? $product['appReduction'] : 0;
                                        $price_ligne = $product['price'] - ($product['price'] * $reduction / 100);
                                        if ($reduction > 0):
                                        ?>
                                            <span class="price-original small"><?= $product['price'] ?> DH</span>
                                            <span class="price-reduced"><?= $price_ligne ?> DH</span>
                                        <?php else: ?>
                                            <span class="fw-bold"><?= $product['price'] ?> DH</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="quantity-info text-primary">
                                        <span class="quantity-label">Quantité:</span>
                                        <span class="quantity-value"><?= $product['quantity'] ?></span>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="summary-shipping mb-4">
                    <h5 class="mb-3"><i class="fas fa-truck me-2"></i> Livraison</h5>
                    <div class="summary-item">
                        <div>
                            <div class="fw-bold">Adresse de livraison</div>
                            <div class="delivery-address text-muted"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total:</span>
                            <span><?= $realTotal ?> DH</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Remises:</span>
                            <span>-<?= $totalDiscount ?> DH</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total à payer:</span>
                            <span class="total-price text-success"><?= $totalAfterDiscount ?> DH</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-phone-alt"></i> Un membre de notre équipe vous contactera au <strong><span id="confirmation-phone"></span></strong> pour confirmer votre commande.
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" id="back-to-shipping" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Modifier
                    </button>
                    <button type="button" id="confirm-order" class="btn btn-custom btn-lg">
                        <i class="fas fa-check"></i> Confirmer la commande
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation finale (apparaît après soumission) -->
    <div id="confirmation-section" style="display: none;">
        <div class="card shadow">
            <div class="card-body">
                <div class="order-confirmation animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle animate__animated animate__bounceIn"></i>
                    <h3 class="mb-3">Commande enregistrée !</h3>
                    <p>Merci pour votre achat. Votre commande <strong>#<span id="order-number">12345</span></strong> a été prise en compte.</p>
                    <p>Un email de confirmation a été envoyé à <strong><span id="confirmation-email">email@example.com</span></strong>.</p>
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-headset me-2"></i> Un membre de notre équipe vous contactera très prochainement au <strong><span id="confirmation-phone-final"></span></strong> pour confirmer les détails de votre commande.
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="/" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="custom-toast" style="z-index: 2001;">
    <p>Voulez-vous vraiment supprimer cet article ?</p>
    <button id="toast-confirm">Supprimer</button>
    <button id="toast-cancel">Annuler</button>
</div>
