<pre>
<?php
foreach ($data['pages'] as $page) {
    ${$page['page_title']} = $page;
}
$selectedProductIds = [];
if (!empty($home['trending_products'])) {
    $selectedProductIds = explode(',', $home['trending_products']);
}
// Récupérer la bannière
$banner = !empty($home['page_data']) ? json_decode($home['page_data'], true) : ["titre1" => "", "titre2" => "", "description" => ""];
?>
</pre>
<div class="card collapse" id="homeManage" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <!-- <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#homeManage" aria-label="Close"></button> -->
        <ul class="nav nav-tabs card-header-tabs" id="homeTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="meta-tab" data-bs-toggle="tab" href="#meta-content" role="tab" aria-controls="meta-content" aria-selected="true">Meta</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="banner-tab" data-bs-toggle="tab" href="#banner-content" role="tab" aria-controls="banner-content" aria-selected="false">Banniere</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="trending-product-tab" data-bs-toggle="tab" href="#trending-product-con" role="tab" aria-controls="trending-product-con" aria-selected="false">Trending products</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="top-categories-tab" data-bs-toggle="tab" href="#top-categories-content" role="tab" aria-controls="top-categories-content" aria-selected="false">Top Categories</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="homeTabContent">
            <!-- Meta part -->
            <div class="tab-pane fade show active" id="meta-content" role="tabpanel" aria-labelledby="meta-tab">
                <div class="card meta-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Meta</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto; overflow-x: hidden;">
                        <div class="mb-3">
                            <label for="homeMetaDesc" class="form-label">Description</label>
                            <div class="input-group">
                                <textarea class="form-control" id="homeMetaDesc"><?= trim($home['page_meta_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="homeMetaKeys" class="form-label">Tags</label>
                            <div class="input-group">
                                <div class=" text-muted alert alert-info">
                                    <small>
                                        <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                        <br>
                                        Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                    </small>
                                </div>
                                <textarea class="form-control meta_tags_input" id="homeMetaKeys"><?= trim($home['page_meta_keywords']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="homeMetaAuth" class="form-label">Author</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="homeMetaAuth" value="<?= trim($home['meta_author']) ?>" />
                            </div>
                        </div>
                    </div>
                    <!-- Boutons du formulaire -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">Fermer</button>
                        <button type="submit" id="setMeta" class="btn btn-primary setMeta" data-page="home">Modifier</button>
                    </div>
                </div>
            </div>
            <!-- Banner part -->
            <div class="tab-pane fade" id="banner-content" role="tabpanel" aria-labelledby="banner-tab">
                <div class="card meta-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Bannière</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto; overflow-x: hidden;">
                        <div class="mb-3">
                            <label for="bannerTitleSmall" class="form-label">Titre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="bannerTitleSmall" value="<?= htmlentities(trim($banner['titre1'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bannerTitleBig" class="form-label">Gros titre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="bannerTitleBig" value="<?= htmlentities(trim($banner['titre2'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bannerDescription" class="form-label">Description</label>
                            <div class="input-group">
                                <textarea class="form-control" id="bannerDescription"><?= htmlentities(trim($banner['description'])) ?? "" ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bannerProduct" class="form-label">Produit</label>
                            <div class="input-group">
                                <?php if (is_array($data['products']) &&  !empty($data['products'])): ?>
                                    <div class="form-group">
                                        <label for="productSearch">Rechercher un produit :</label>
                                        <div class="dropdown">
                                            <input type="text" id="productSearch" class="form-control" placeholder="Rechercher un produit..." autocomplete="off">
                                            <ul id="productList" class="dropdown-menu" style="height: 200px; overflow: auto; width: 100%;">
                                                <?php
                                                $bannerProd = null;
                                                foreach ($data['products'] as $product):
                                                    if ($product['id'] == trim($banner['productid'])) {
                                                        $bannerProd = $product;
                                                    }
                                                    if ($product['status'] == "affiche"): ?>
                                                        <li class="dropdown-item" data-info='
                                  <?= json_encode([
                                                            'id' => $product['id'],
                                                            'title' => $product['title'],
                                                            'price' => $product['price'],
                                                            'status' => $product['status'],
                                                        ]) ?>'>
                                                            <?= $product['title'] ?>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <!-- Détails du produit sélectionné -->
                                        <div id="productDetails" class="mt-4">
                                            <label class="form-label">Produit Selectionné</label>
                                            <div class="product-info" id="productInfo">
                                                <p><strong>Titre:</strong> <?= " " . trim($bannerProd['title'] ?? "Pas selectionné") ?></p>
                                                <p><strong>Prix:</strong><?= " " . trim($bannerProd['price'] ?? "Pas selectionné") ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Boutons du formulaire -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">Fermer</button>
                        <button type="submit" id="modifyBanner" class="btn btn-primary">Modifier</button>
                    </div>
                </div>
            </div>
            <!-- Trending products part -->
            <div class="tab-pane fade" id="trending-product-con" role="tabpanel" aria-labelledby="trending-product-tab">
                <div class="card trending-part">
                    <div class="card-header">
                        <h5 class="card-title">Produits en Tendance</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                        <div class="product-grid-wrapper" style="max-height: 300px; overflow-y: auto;">
                            <div class="m-3 p-3" id="trending-products-grid"></div>
                        </div>
                    </div>

                    <!-- Boutons du formulaire -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">Fermer</button>
                    </div>
                </div>
            </div>
            <!-- Trending categories part -->
            <div class="tab-pane fade" id="top-categories-content" role="tabpanel" aria-labelledby="top-categories-tab">

                <div class="card shadow-sm">
                    <div class="card-header bg-gradient">
                        <h5 class="card-title mb-0">Top Categories</h5>
                    </div>
                    <div class="card-body" style="max-height: 480px; overflow-y: auto;">
                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            </button>
                            <strong>Note : </strong> Les catégories en tendance ne seront affichées que si vous cochez au moins 4
                        </div>
                        <style>
                            .category-card.selected {
                                border: 2px solid #3498db;
                                transform: translateY(-3px);
                                transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                                border-radius: 8px;
                                padding: 3px;
                            }

                            .category-card:hover {
                                border-color: #2980b9;
                                box-shadow: 0 6px 14px rgba(41, 128, 185, 0.6);
                                background: linear-gradient(120deg, #b3e5fc, #81d4fa);
                                transform: translateY(-5px);
                            }
                        </style>
                        <div class="selected-categories-grid m-3 p-3" id="selectedCategories"></div>
                    </div>

                    <!-- Boutons de contrôle -->
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card collapse" id="shopAreaManage" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="contactTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="contact-meta-tab" data-bs-toggle="tab" href="#contact-meta-content" role="tab" aria-controls="contact-meta-content" aria-selected="true">Meta</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="shopTabContent">
            <!-- Meta part -->
            <div class="tab-pane fade show active" id="shop-meta-content" role="tabpanel" aria-labelledby="shop-meta-tab">
                <div class="card meta-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Meta</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                        <div class="mb-3">
                            <label for="shopMetaDesc" class="form-label">Description</label>
                            <div class="input-group">
                                <textarea class="form-control" id="shopMetaDesc"><?= trim($shop['page_meta_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shopMetaKeys" class="form-label">Tags</label>
                            <div class="input-group">
                                <div class=" text-muted alert alert-info">
                                    <small>
                                        <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                        <br>
                                        Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                    </small>
                                </div>
                                <textarea class="form-control meta_tags_input" id="shopMetaKeys"><?= trim($shop['page_meta_keywords']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#shopAreaManage">Fermer</button>
                        <button type="submit" id="setshopMeta" class="btn btn-primary setMeta" data-page="shop">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card collapse" id="contactAreaManage" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="contactTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="contact-meta-tab" data-bs-toggle="tab" href="#contact-meta-content" role="tab" aria-controls="contact-meta-content" aria-selected="true">Meta</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="contact-info-tab" data-bs-toggle="tab" href="#contact-info-content" role="tab" aria-controls="contact-info-content" aria-selected="false">Contact Info</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="contactTabContent">
            <!-- Meta part -->
            <div class="tab-pane fade show active" id="contact-meta-content" role="tabpanel" aria-labelledby="contact-meta-tab">
                <div class="card meta-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Meta</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                        <div class="mb-3">
                            <label for="contactMetaDesc" class="form-label">Description</label>
                            <div class="input-group">
                                <textarea class="form-control" id="contactMetaDesc"><?= trim($contact['page_meta_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactMetaKeys" class="form-label">Tags</label>
                            <div class="input-group">
                                <div class=" text-muted alert alert-info">
                                    <small>
                                        <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                        <br>
                                        Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                    </small>
                                </div>
                                <textarea class="form-control meta_tags_input" id="contactMetaKeys"><?= trim($contact['page_meta_keywords']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactMetaAuth" class="form-label">Author</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="contactMetaAuth" value="<?= trim($contact['meta_author']) ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#contactAreaManage">Fermer</button>
                        <button type="submit" id="setContactMeta" class="btn btn-primary setMeta" data-page="contact">Modifier</button>
                    </div>
                </div>
            </div>
            <!-- Contact Info part  -->
            <div class="tab-pane fade" id="contact-info-content" role="tabpanel" aria-labelledby="contact-info-tab">
                <div class="card contact-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Contact</h5>
                    </div>
                    <?php
                    $contactInfo = !empty($contact['page_data']) ? json_decode($contact['page_data'], true) : ["introduction" => "", "address" => "", "phone" => "", "email" => ""];
                    ?>

                    <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                        <div class="mb-3">
                            <label for="contactAddress" class="form-label">Titre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="contactTitle" value="<?= htmlentities(trim($contactInfo['address'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactIntro" class="form-label">Introduction</label>
                            <div class="input-group">
                                <textarea class="form-control" id="contactIntro"><?= htmlentities(trim($contactInfo['introduction'])) ?? "" ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactAddress" class="form-label">Address</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="contactAddress" value="<?= htmlentities(trim($contactInfo['address'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactPhone" class="form-label">Phone</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="contactPhone" value="<?= htmlentities(trim($contactInfo['phone'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">Email</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="contactEmail" value="<?= htmlentities(trim($contactInfo['email'])) ?? "" ?>" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactMap" class="form-label">Localisation</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="contactMap" value="<?= htmlentities(trim($contactInfo['map'])) ?? "" ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#contactAreaManage">Fermer</button>
                        <button type="submit" id="updateContact" class="btn btn-primary">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card collapse" id="articlesAreaManage" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="articlesTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="articles-meta-tab" data-bs-toggle="tab" href="#articles-meta-content" role="tab" aria-controls="articles-meta-content" aria-selected="true">Meta</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="articlesTabContent">
            <!-- Meta part -->
            <div class="tab-pane fade show active" id="articles-meta-content" role="tabpanel" aria-labelledby="articles-meta-tab">
                <div class="card meta-part">
                    <div class="card-header">
                        <h5 class="card-title">Modification Meta</h5>
                    </div>
                    <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                        <div class="mb-3">
                            <label for="articlesMetaDesc" class="form-label">Description</label>
                            <div class="input-group">
                                <textarea class="form-control" id="articlesMetaDesc"><?= trim($articles['page_meta_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="articlesMetaKeys" class="form-label">Tags</label>
                            <div class="input-group">
                                <div class=" text-muted alert alert-info">
                                    <small>
                                        <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                        <br>
                                        Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                    </small>
                                </div>
                                <textarea class="form-control meta_tags_input" id="articlesMetaKeys"><?= trim($articles['page_meta_keywords']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#articlesAreaManage">Fermer</button>
                        <button type="submit" id="setarticlesMeta" class="btn btn-primary setMeta" data-page="articles">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Log -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logModalLabel"><i class="fas fa-clipboard-list"></i> Journal d'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="logTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="log-tab" data-bs-toggle="tab" href="#log-tab-content" role="tab" aria-controls="log-tab-content" aria-selected="true">Log</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="logTabContent">
                    <div class="tab-pane fade show active" id="log-tab-content" role="tabpanel" aria-labelledby="log-tab">
                        <div class="card meta-part">
                            <div class="card-header">
                                <h5 class="card-title">Filtrage</h5>
                            </div>
                            <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                                <div class="mb-3">
                                    <label for="logLevel" class="form-label">Level</label>
                                    <select class="form-control" id="logLevel">
                                        <option>ALL</option>
                                        <option>INFO</option>
                                        <option>WARNING</option>
                                        <option>ERROR</option>
                                        <option>DEBUG</option>
                                        <option>CRITICAL</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="logDateRange" class="form-label">Date</label>
                                    <select class="form-control" id="logDateRange">
                                        <option>All</option>
                                        <option>Day</option>
                                        <option>Week</option>
                                        <option>Month</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="logSearch" class="form-label">Recherche</label>
                                    <input type="text" class="form-control" id="logSearch" placeholder="Mot-clé...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="showLog">Afficher</button>
                <button type="button" class="btn btn-danger" id="delLog">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                    Vider le log</button>
            </div>
        </div>
    </div>
</div>