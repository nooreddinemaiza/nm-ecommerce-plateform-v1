<?php

use Src\Helpers\Helper;
use Src\Helpers\UrlHelper;
use Src\Helpers\ShopHelper;

Helper::safeJsonDecode($data, "searchProductList");
Helper::safeJsonDecode($data, "search");
?>
<div class="main-banner">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="caption header-text">
          <h2>Résultat de la recherche pour : '<span style="color: #ee626b;"><?= htmlspecialchars($data['search']) ?></span>'</h2>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="section trending">
  <div class="container">
    <div class="row">
      <div class="col-lg-8">
        <div class="section-heading">
          <h6>Résultat</h6>
        </div>
      </div>

      <?php if (!empty($search)) {
        $paginationData = $searchProductList['pagination'] ?? [];
        $currentPage = $paginationData['current_page'] ?? 1;
        $totalPages = $paginationData['total_pages'] ?? 1;
        $hasProducts = !empty($searchProductList['products']);
        $products = $hasProducts ? Helper::combineDataByKey($searchProductList['products'], 'id') : [];
      ?>
        <!-- Grille de produits -->
        <div class="row">
          <?php if ($hasProducts): ?>
            <?php foreach ($products as $product): ?>
              <?php
              // Récupérer les catégories du produit
              $categoriesForProduct = ShopHelper::getProductCategories($product);
              $imageFileName = ShopHelper::getMainImage($product);
              $link = UrlHelper::generateProductLink(
                htmlspecialchars($product['slug']),
                $product['id']
              );
              // Générer les classes CSS pour Isotope
              $productCategoryClasses = implode(' ', array_map(function ($category) {
                return htmlspecialchars(str_replace(' ', '_', strtolower($category)));
              }, $categoriesForProduct));
              ?>
              <div class="col-lg-3 col-md-6 align-self-center mb-30 trending-items <?= $productCategoryClasses ?>">
                <div class="item">
                  <div class="thumb">
                    <a href="<?= $link ?>">
                      <img
                        src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>"
                        alt="<?= htmlspecialchars($product['title']) ?>"
                        style="height: 195px;">
                    </a>
                    <span class="price">
                      <?php if (ShopHelper::hasDiscount($product)): ?>
                        <em><?= htmlspecialchars($product['old_price']) ?></em>
                      <?php endif; ?>
                      <?= htmlspecialchars($product['price']) ?>
                    </span>
                  </div>
                  <div class="down-content">
                    <span class="category"><?= htmlspecialchars(implode(', ', $categoriesForProduct)) ?></span>
                    <h4 style="min-height: 60px;"><?= htmlspecialchars($product['title']) ?></h4>
                    <a href="<?= $link ?>" ><i class="fa fa-shopping-bag"></i></a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif;
          if ($totalPages > 1) {
          ?>

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
          <?php
          }
          ?>
        </div>
    </div>
  </div>
</div>
<?php } ?>

<div class="section most-played">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="section-heading">
          <h6>Produits récents</h6>
          <h2>Produits pour vous</h2>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="main-button">
          <a href="/shop">Explorer</a>
        </div>
      </div>
      <?php
      if (!empty($productList)) {
        $productList = Helper::combineDataByKey($productList, "id");
        $productList = array_slice($productList, 0 , 6);
        foreach ($productList as $product) {
            $link = UrlHelper::generateProductLink(
              htmlspecialchars($product['slug']),
              $product['id']
            );
            $image = is_array($product['images'])
              ? ($product['images'][0] ?? 'No_Image_Available.jpg')
              : ($product['images'] ?? 'No_Image_Available.jpg');
            $imageParts = explode('|', $image);
            $imageFileName = $imageParts[1] ?? $image;
            $category = "";
      ?>
            <div class="col-lg-2 col-md-6 col-sm-6">
              <div class="item">
                <div class="thumb">
                  <a href="<?= $link ?>"><img src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>" title="<?= htmlspecialchars($product['title']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" style="height: 230px;"></a>
                </div>
                <div class="down-content">
                  <span class="category"><?= htmlspecialchars($category) ?></span>
                  <h4 style="min-height: 60px;"><?= htmlspecialchars($product['title']) ?></h4>
                  <a href="<?= $link ?>">Explore</a>
                </div>
              </div>
            </div>
      <?php
        }
      }
      ?>
    </div>
  </div>
</div>