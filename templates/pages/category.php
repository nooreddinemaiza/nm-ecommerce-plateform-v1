<?php

use Src\Helpers\UrlHelper;
use Src\Helpers\ShopHelper;
use Src\Helpers\FileAndPathManager;
?>

<div class="main-banner page-heading header-text" style="padding-bottom: 100px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <span class="breadcrumb">
                    <a href="/">Acceuil</a> > <a href="/categories">Categories</a> > <?= htmlspecialchars($category['title']) ?>
                </span>
            </div>
            <div class="col-lg-6">
                <div class="caption header-text">
                    <h3><?= htmlspecialchars_decode($category['title']); ?></h3>
                    <p><?= nl2br(htmlspecialchars_decode($category['description'])); ?></p>
                </div>
            </div>
            <div class="col-lg-4 offset-lg-2">
                <div class="right-image">
                    <?php
                    $image = $category['image'] ? $category['image'] : "No_Image_Available.jpg";
                    $image = "category-image/" . (FileAndPathManager::fileExists('category-image', $image) ? $image : 'unfound.jpg');
                    ?>
                    <img src="/assets/images/<?= ($image) ?>" alt="" title="<?= htmlspecialchars($category['title']) ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section trending">
    <div class="container">
        <div class="col-lg-8">
            <div class="section-heading">
                <h2>Produits de la categorie: </h2>
            </div>
        </div>
        <!-- Grille de produits -->
        <div class="row trending-box">
            <?php if (!empty($productList)): ?>
                <?php foreach ($productList as $product): ?>
                    <div class="col-lg-3 col-md-6 align-self-center mb-30 trending-items">
                        <div class="item">
                            <!-- Image du produit -->
                            <div class="thumb">
                                <a href="<?= UrlHelper::generateProductLink($product['slug'], $product['id']) ?>"
                                    class="product-title product-link"
                                    data-title="<?= htmlspecialchars($product['title']) ?>">
                                    <?php
                                    $image = (explode(',', $product['images']));
                                    $image = $image[rand(0, count($image) - 1)];
                                    $image = FileAndPathManager::fileExists('product-image', $image) ? $image : 'No_Image_Available.jpg';
                                    ?>
                                    <img
                                        src="/assets/images/product-image/<?= $image ?>"
                                        alt="<?= htmlspecialchars($product['title']) ?>"
                                        title="<?= htmlspecialchars($product['title']) ?>"
                                        style="height: 195px;">
                                </a>
                                <!-- Prix -->
                                <span class="price">
                                    <?php if (ShopHelper::hasDiscount($product)): ?>
                                        <em><?= htmlspecialchars($product['old_price']) ?></em>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($product['price']) ?>
                                </span>
                            </div>
                            <!-- Détails du produit -->
                            <div class="down-content" style="height: 40px;">
                                <h4>
                                    <a href="<?= $link ?>"
                                        class="product-title product-link"
                                        data-title="<?= htmlspecialchars($product['title']) ?>"
                                        style="width: 90% !important;background: none;color: black;border-radius: initial;">
                                        <?= htmlspecialchars($product['title']) ?>
                                    </a>
                                </h4>
                                <a href="<?= UrlHelper::generateProductLink($product['slug'], $product['id']) ?>">
                                    <i class="fa fa-shopping-bag"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <p>Aucun produit trouvé dans cette catégorie.</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- Pagination -->
        <div class="row">
            <div class="col-lg-12">
                <ul class="pagination">
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <!-- Bouton précédent -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li>
                                <a href="<?= UrlHelper::generateUrl(['page' => $pagination['current_page'] - 1]) ?>" aria-label="Previous">
                                    &lt;
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        // Calculer la plage de pages à afficher
                        $range = 2;
                        $startPage = max(1, $pagination['current_page'] - $range);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + $range);

                        // Première page si on n'y est pas
                        if ($startPage > 1): ?>
                            <li><a href="<?= UrlHelper::generateUrl(['page' => 1]) ?>">1</a></li>
                            <?php if ($startPage > 2): ?>
                                <li><span class="dots">...</span></li>
                            <?php endif;
                        endif;

                        // Pages numérotées
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li>
                                <a href="<?= UrlHelper::generateUrl(['page' => $i]) ?>"
                                    <?= $i === $pagination['current_page'] ? 'class="is_active"' : '' ?>>
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor;

                        // Dernière page si nécessaire
                        if ($endPage < $pagination['total_pages']): ?>
                            <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                <li><span class="dots">...</span></li>
                            <?php endif; ?>
                            <li><a href="<?= UrlHelper::generateUrl(['page' => $pagination['total_pages']]) ?>"><?= $pagination['total_pages'] ?></a></li>
                        <?php endif;

                        // Bouton suivant
                        if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li>
                                <a href="<?= UrlHelper::generateUrl(['page' => $pagination['current_page'] + 1]) ?>" aria-label="Next">
                                    &gt;
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>