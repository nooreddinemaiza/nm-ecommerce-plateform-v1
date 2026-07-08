<?php

use Src\Helpers\Helper;
use Src\Helpers\UrlHelper;
use Src\Helpers\ShopHelper;
use Src\Helpers\FileAndPathManager;
// Vérification et préparation des données
$categories = [];
$paginationData = $productList['pagination'] ?? [];
$currentPage = $paginationData['current_page'] ?? 1;
$totalPages = $paginationData['total_pages'] ?? 1;
$hasProducts = !empty($productList['products']);
$products = $hasProducts ? Helper::combineDataByKey($productList['products'], 'id') : [];
if ($hasProducts) {
    $categories = ShopHelper::extractCategories($products);
}
?>
<div class="page-heading header-text" style="background-color:rgb(0, 89, 255);">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Notre Shop</h3>
                <span class="breadcrumb"><a href="/">Acceuil</a> >Shop</span>
            </div>
        </div>
        <div class="row" style="margin: 0 60px; ">
            <div class="search-input-container" style="margin: 30px auto !important; margin-bottom: 0 !important;">
                <input type="text" placeholder="Recherchez un produit" id='searchText' name="searchText" />
                <button type="button" onclick="handleSearch()"><i class="fa fa-search" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<?php
\Src\Helpers\FileAndPathManager::includeFile('part', 'search-result.html');
$sectR = new Src\Helpers\SectionRenderer('shop');
echo $sectR->header();
?>
<div class="section trending">
    <div class="container">
        <div class="row">
            <!-- Colonne de gauche pour les filtres -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar-filters" *>
                    <h4 class="mb-3">Filtres</h4>
                    <ul class="trending-filter list-unstyled">
                        <li class="mb-2">
                            <a class="btn btn-sm w-100 is_active" href="#!" data-filter="*">Tous</a>
                        </li>
                        <?php if ($hasProducts): ?>
                            <?php foreach ($categories as $category): ?>
                                <li class="mb-2">
                                    <a class="btn btn-sm w-100" href="#!" data-filter=".<?= htmlspecialchars(str_replace(' ', '_', strtolower($category))) ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', strtolower($category))) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Colonne de droite pour les produits -->
            <div class="col-lg-9 col-md-8">
                <!-- Grille de produits -->
                <div class="row trending-box">
                    <?php if ($hasProducts): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            // Récupérer les catégories du produit
                            $categoriesForProduct = ShopHelper::getProductCategories($product);
                            $imageFileName = ShopHelper::getMainImage($product);
                            $randomImage = FileAndPathManager::fileExists('product-image', $imageFileName) ? $imageFileName : 'No_Image_Available.jpg';
                            $link = UrlHelper::generateProductLink(
                                htmlspecialchars($product['slug']),
                                $product['id']
                            );
                            // Générer les classes CSS pour Isotope
                            $productCategoryClasses = implode(' ', array_map(function ($category) {
                                return htmlspecialchars(str_replace(' ', '_', strtolower($category)));
                            }, $categoriesForProduct));
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4 trending-items <?= $productCategoryClasses ?>"
                                style="height: 270px !important;overflow: hidden !important;">
                                <div class="item card h-100">
                                    <div class="thumb position-relative">
                                        <a href="<?= $link ?>" class="product-link" data-title="<?= htmlspecialchars($product['title']) ?>">
                                            <img
                                                class="card-img-top"
                                                src="/assets/images/product-image/<?= htmlspecialchars($randomImage) ?>"
                                                alt="<?= htmlspecialchars($product['title']) ?>"
                                                style="height: 180px; object-fit: cover;">
                                        </a>
                                        <span class="price position-absolute badge bg-primary" style="right: 10px; top: 10px;">
                                            <?php if (ShopHelper::hasDiscount($product)): ?>
                                                <em class="text-decoration-line-through me-2"><?= htmlspecialchars($product['old_price'] ?? 0) ?></em>
                                            <?php endif; ?>
                                            <?= htmlspecialchars(($product['price'] ?? 0) > 0 ? $product['price'] : 0) ?>
                                        </span>
                                    </div>
                                    <div class="card-body down-content d-flex flex-column">
                                        <h5 class="card-title" style="margin-top: 15px !important;font-size: large !important;">
                                            <a href="<?= $link ?>"
                                                class="truncate product-title product-link"
                                                data-title="<?= htmlspecialchars($product['title']) ?>"
                                                style="position: initial;width: 100% !important;line-height: initial;background: none;color: black;text-align: left;font-size: initial;border-radius: initial;">
                                                <?= htmlspecialchars($product['title']) ?>
                                            </a>
                                        </h5>
                                        <div class="mt-auto text-end">
                                            <a href="<?= $link ?>" class="btn btn-sm d-flex align-items-center justify-content-center gap-2 product-link" title="<?= htmlspecialchars($product['title']) ?>">
                                                <i class="fa fa-shopping-bag"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Pagination -->
                <div class="row">
                    <div class="col-lg-12">
                        <ul class="pagination">
                            <?php
                            if ($totalPages > 1): ?>
                                <!-- Bouton précédent -->
                                <?php if ($currentPage > 1): ?>
                                    <li><a href="<?= UrlHelper::generateUrl(['page' => $currentPage - 1]) ?>" aria-label="Previous"> &lt; </a></li>
                                <?php endif; ?>

                                <?php
                                // Calculer la plage de pages à afficher
                                $range = 2;
                                $startPage = max(1, $currentPage - $range);
                                $endPage = min($totalPages, $currentPage + $range);

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
                                            <?= $i === $currentPage ? 'class="is_active"' : '' ?>>
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor;

                                // Dernière page si nécessaire
                                if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li><span class="dots">...</span></li>
                                    <?php endif; ?>
                                    <li><a href="<?= UrlHelper::generateUrl(['page' => $totalPages]) ?>"><?= $totalPages ?></a></li>
                                <?php endif;

                                // Bouton suivant
                                if ($currentPage < $totalPages): ?>
                                    <li><a href="<?= UrlHelper::generateUrl(['page' => $currentPage + 1]) ?>" aria-label="Next"> &gt; </a></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $sectR->renderAllUnrenderedSections(); ?>
<script>
    $(document).ready(function() {
        // Wait a moment to ensure all elements are fully loaded
        setTimeout(function() {
            // Initialize Isotope with jQuery selector
            var $grid = $('.trending-box');

            if ($grid.length > 0) {
                // Initialize Isotope with jQuery for better compatibility
                $grid.isotope({
                    itemSelector: '.trending-items',
                    layoutMode: 'fitRows'
                });

                // Filter functionality
                $('.trending-filter a').on('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all buttons
                    $('.trending-filter a').removeClass('is_active');

                    // Add active class to clicked button
                    $(this).addClass('is_active');

                    // Get filter value
                    var filterValue = $(this).attr('data-filter');

                    // Apply filter
                    $grid.isotope({
                        filter: filterValue
                    });

                    return false;
                });
            }
        }, 100); // Small delay to ensure everything is loaded
    });
</script>