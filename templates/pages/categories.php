<?php

use Src\Helpers\UrlHelper;
use Src\Helpers\FileAndPathManager;
?>
<div class="page-heading header-text" style="background: #0071f8;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Decouvrez nos categories</h3>
                <span class="breadcrumb"><a href="/">Acceuil</a> > categories</span>
            </div>
        </div>
        <div class="row search-input">
            <div class="search-input-container" style="margin: 30px auto !important;">
                <input type="text" placeholder="Recherchez un produit" id='searchText' name="searchText" />
                <button type="button" onclick="handleSearch()"><i class="fa fa-search" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>
<?php
if (count($categoriesList) > 3) {
?>
    <div class="features d-none d-lg-block">
        <style>
            .feature-item .item:hover {
                background-image: linear-gradient(to bottom, rgb(0, 104, 231), rgb(147, 196, 255));
            }
        </style>
        <div class="container">
            <div class="row">
                <?php
                $featured = array_slice($categoriesList, 0, 4);
                foreach (($featured) as $categorie) {
                    $randomImage = FileAndPathManager::fileExists('category-image', $categorie['image_path']) ? $categorie['image_path'] : 'product-image/No_Image_Available.jpg';
                ?>
                    <div class="col-lg-3 col-md-6 feature-item">
                        <a href="#<?= $categorie['title'] ?>">
                            <div class="item" style="height: 210px !important;">
                                <div class="image" style="overflow: hidden;">
                                    <img src="/assets/images/<?= $randomImage ?>" alt="" style="width: 140px;">
                                </div>
                                <h4><?= htmlspecialchars($categorie['title']) ?></h4>
                            </div>
                        </a>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <pre>
        <?php
        ?>
    </pre>
<?php
}
FileAndPathManager::includeFile('part', 'search-result.html');
?>

<div class="section trending">
    <div class="container">

        <?php
        if (!empty($categoriesList)) {
            foreach ($categoriesList as $categorie) {
                $CategorieTitle = htmlspecialchars($categorie['title']);
                $categorieLink = UrlHelper::generateCategoryLink($CategorieTitle);
        ?>
                <hr class="my-4" id="<?= $CategorieTitle ?>">
                <div class="row">
                    <div class="row">
                        <div class="col-lg-6 section-heading">
                            <h2><?= $CategorieTitle ?></h2>
                        </div>
                        <div class="col-lg-6">
                            <div class="main-button">
                                <a href="<?= $categorieLink ?>">Explorer</a>
                            </div>
                        </div>
                    </div>
                    <div class="row trending-box">
                        <?php
                        if (!empty($productsList)) {
                            $count = 0; // Initialisation du compteur

                            foreach ($productsList as $product) {
                                $productCategories = \Src\Helpers\ShopHelper::getProductCategories($product);

                                if (in_array($CategorieTitle, $productCategories)) {
                                    if ($count >= 4) {
                                        break; // On arrête après 4 produits affichés
                                    }

                                    list($imgId, $path) = explode('|', is_array($product['images']) ? $product['images'][0] : $product['images']);
                                    $imageFileName = $path;
                                    $randomImage = FileAndPathManager::fileExists('product-image', $imageFileName) ? $imageFileName : 'No_Image_Available.jpg';

                                    $productLink = UrlHelper::generateProductLink($product['slug'], $product['id']);
                        ?>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="item" style="height: 320px;">
                                            <div class="thumb">
                                                <a href="<?= $productLink ?>" class="product-title product-link"
                                                    data-title="<?= htmlspecialchars($product['title']) ?>">
                                                    <img src="/assets/images/product-image/<?= htmlspecialchars($randomImage) ?>"
                                                        title="<?= htmlspecialchars($product['title']) ?>"
                                                        style="height: 230px;">
                                                </a>
                                                <span class="price">
                                                    <em><?= ($product['old_price'] != $product['price']) ? htmlspecialchars($product['old_price']) : "" ?></em>
                                                    <?= htmlspecialchars($product['price']) ?>
                                                </span>
                                            </div>
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
                        <?php
                                    $count++;
                                }
                            }
                        }
                        ?>

                    </div>
            <?php
            }
        }
            ?>
                </div>
    </div>
</div>