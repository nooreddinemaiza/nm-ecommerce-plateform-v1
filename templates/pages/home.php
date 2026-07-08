<?php

use Src\Helpers\FileAndPathManager;
use Src\Helpers\Helper;
use Src\Helpers\UrlHelper;


$data = Helper::safeJsonDecode($data, "productList");
$data = Helper::safeJsonDecode($data, "pageContent");
$data = Helper::safeJsonDecode($data, "categorieList");
foreach (array_keys($data) as $k => $v) {
  ${$v} = $data[$v];
}
$banner = json_decode($pageContent[0]['page_data'], true);
$bannerProduct = null;
$trendingProducts = [];
$productList = Helper::combineDataByKey($productList, 'id');

$sectR = new Src\Helpers\SectionRenderer('home');

foreach ($productList as $product) {
  if ($product['is_trend']) {
    $trendingProducts[] = $product;
  }
  if ($product['id'] == (int)$banner['productid']) {
    $bannerProduct = $product;
  }
}
?>
<div class="main-banner" style="padding-bottom: 100px;">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 align-self-center">
        <div class="caption header-text">
          <h6><?= htmlspecialchars_decode($banner['titre1']); ?></h6>
          <h2><?= htmlspecialchars_decode($banner['titre2']); ?></h2>
          <p><?= nl2br(htmlspecialchars_decode($banner['description'])); ?></p>
        </div>
      </div>
      <?php
      if (!is_null($bannerProduct)) {
      ?>
        <div class="col-lg-4 offset-lg-2">
          <div class="right-image">
            <?php
            $image = is_array($bannerProduct['images']) ? ($bannerProduct['images'][0]) : ($bannerProduct['images'] ?? 'No_Image_Available.jpg');
            $imageParts = explode('|', $image);
            $imageFileName = $imageParts[1] ?? $image;
            $imageFileName = FileAndPathManager::fileExists('product-image', $imageFileName) ? $imageFileName : 'No_Image_Available.jpg';
            $link = UrlHelper::generateProductLink($bannerProduct['slug'], $bannerProduct['id']);
            ?>
            <a href="<?= $link ?>">
              <img src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>" style="height: 300Px;" alt="" title="<?= htmlspecialchars($bannerProduct['title']) ?>">
            </a>
            <span class="price"><?= (int)($bannerProduct['price']); ?> DH</span>
            <?= $bannerProduct['reduction'] != 0 ? "<span class='offer'>-$bannerProduct[reduction]%</span>" : ""; ?>
          </div>
        </div>

      <?php
      }
      ?>
    </div>
  </div>
  <div class="row" style="margin: 60px; margin-bottom: 0 !important;">
    <div class="search-input-container" style="margin: 30px auto !important; margin-bottom: 0 !important;">
      <input type="text" placeholder="Recherchez un produit" id='searchText' name="searchText" />
      <button type="button" onclick="handleSearch()"><i class="fa fa-search" aria-hidden="true"></i></button>
    </div>
  </div>
</div>
<?php
if (!empty($categorieList) && count($categorieList) >= 4) {
  $categorieList = array_slice($categorieList, 0, 4);
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
        foreach ($categorieList as $categorie) {
          $link = UrlHelper::generateCategoryLink(htmlspecialchars($categorie['title']));
          $imageFileName = $categorie['image_path'];
          $imageFileName = FileAndPathManager::fileExists('category-image', $imageFileName) ? ("/assets/images/category-image/" . $imageFileName) : '/assets/images/product-image/No_Image_Available.jpg';
        ?>
          <div class="col-lg-3 col-md-6 feature-item">
            <a href="<?= $link ?>">
              <div class="item" style="height: 210px !important;">
                <div class="image" style="overflow: hidden;">
                  <img src="<?= $imageFileName ?>" alt="" style="width: 140px;">
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
<?php
}
\Src\Helpers\FileAndPathManager::includeFile('part', 'search-result.html');
echo $sectR->header();
?>

<div class="section trending">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="section-heading">
          <h6>Tendance</h6>
          <h2>Produits de tendance</h2>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="main-button">
          <a href="/shop">Explorer</a>
        </div>
      </div>
      <?php
      if (!empty($trendingProducts)) {
        foreach ($trendingProducts as $product) {
          $catSpan = false;
          $link = UrlHelper::generateProductLink(htmlspecialchars($product['slug']), $product['id']);
          $image = is_array($product['images'])
            ? ($product['images'][0] ?? 'No_Image_Available.jpg')
            : ($product['images'] ?? 'No_Image_Available.jpg');
          $imageParts = explode('|', $image);
          $imageFileName = $imageParts[1] ?? $image;
          $imageFileName = FileAndPathManager::fileExists('product-image', $imageFileName) ? ($imageFileName) : 'No_Image_Available.jpg';
          $category = is_array($product['categories']) ? ($product['categories'][0] ?? '') : ($product['categories'] ?? '');
      ?>
          <div class="col-lg-3 col-md-6">
            <div class="item" style="height: 320px;">
              <div class="thumb">
                <a href="<?= $link ?>"
                  class="product-title product-link"
                  data-title="<?= htmlspecialchars($product['title']) ?>">
                  <img src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>" style="height: 230px;"></a>
                <span class="price"><em><?= htmlspecialchars($product['old_price'] != $product['old_price'] ? $product['price'] : "") ?></em><?= htmlspecialchars($product['price']) ?></span>
              </div>
              <div class="down-content">
                <?php if (!empty($category)):
                  $catSpan = true;
                ?>
                  <span class="category"><?= htmlspecialchars($category) ?></span>
                <?php
                endif;
                ?>
                <h4 <?= !$catSpan ? 'style="text-align: center;margin-top: 20px;"'  : ""  ?>>
                  <a href="<?= $link ?>"
                    class="product-title product-link"
                    data-title="<?= htmlspecialchars($product['title']) ?>"
                    style="width: 90% !important;background: none;color: black;border-radius: initial;">
                    <?= htmlspecialchars($product['title']) ?>
                  </a>
                </h4>
                <a href="<?= $link ?>"><i class="fa fa-shopping-bag"></i></a>
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
<?= $sectR->renderInOrder(); ?>
<div class="section most-played">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="section-heading">
          <h6>TOP</h6>
          <h2>TOP Produits</h2>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="main-button">
          <a href="/shop">Explorer</a>
        </div>
      </div>
      <?php
      if (!empty($productList)) {
        $i = 0;
        foreach ($productList as $product) {
          if ($i < 6) {
            $image = is_array($product['images'])
              ? ($product['images'][0] ?? 'No_Image_Available.jpg')
              : ($product['images'] ?? 'No_Image_Available.jpg');
            $imageParts = explode('|', $image);
            $imageFileName = $imageParts[1] ?? $image;
            $imageFileName = FileAndPathManager::fileExists('product-image', $imageFileName) ? $imageFileName : 'No_Image_Available.jpg';
            $category = is_array($product['categories']) ? ($product['categories'][0] ?? '') : ($product['categories'] ?? '');
            $link = UrlHelper::generateProductLink(htmlspecialchars($product['slug']), $product['id']);
      ?>
            <div class="col-lg-2 col-md-6 col-sm-6">
              <div class="item">
                <div class="thumb">
                  <a href="<?= $link ?>"
                    class="product-title product-link"
                    title="<?= htmlspecialchars($product['title']) ?>">
                    <img src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>" alt="" title="<?= htmlspecialchars($product['title']) ?>" style="height: 165px;"></a>
                </div>
                <div class="down-content">
                  <h4 class="text-truncate" style="padding: 11px 0;" title="<?= htmlspecialchars($product['title']) ?>"><?= htmlspecialchars($product['title']) ?></h4>
                  <a href="<?= $link ?>">Voir</a>
                </div>
              </div>
            </div>
      <?php
            $i++;
          }
        }
      }
      ?>
    </div>
  </div>
</div>
<?= $sectR->renderInOrder(); ?>
<?php
if (!empty($categorieList)) {
?>
  <div class="section most-played">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6>Top</h6>
            <h2>TOP Catégories</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="/categories" style="float: right;">Explorer</a>
          </div>
        </div>
        <?php
        if (!empty($categorieList)) {
          $i = 0;
          foreach ($categorieList as $categorie) {
            $link = UrlHelper::generateCategoryLink(htmlspecialchars($categorie['title']));
            if ($i < 5) {
              $imageFileName = FileAndPathManager::fileExists('category-image', $categorie['image_path']) ? ("/assets/images/category-image/" . $categorie['image_path']) : '/assets/images/product-image/No_Image_Available.jpg';
        ?>
              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="item">
                  <h4 sstyle="min-height: 50px !important;padding-top: 10px;"><?= htmlspecialchars($categorie['title']) ?></h4>
                  <div class="thumb">
                    <a href="<?= $link ?>"><img style="height: 160px;" src="<?= $imageFileName ?>" alt="<?= htmlspecialchars($categorie['title']) ?>"></a>
                  </div>
                  <div class="down-content" style="padding: 0;">
                    <a href="<?= $link ?>"></i>Explorez</a>
                  </div>
                </div>
              </div>
        <?php
              $i++;
            }
          }
        }
        ?>
      </div>
    </div>
  </div>
<?php
}
?>
<?= $sectR->renderInOrder(); ?>
<div class="section cta">
  <div class="row">
    <div class="col-lg-6">
      <div class="shop">
        <div class="col-lg-12">
          <div class="col-lg-12">
            <div class="section-heading">
              <h6>Notre Boutique</h6>
              <h2>Précommandez et Profitez des <em>Meilleurs Prix</em> Rien que Pour Vous!</h2>
            </div>
            <p>Découvrez notre sélection unique de produits technologiques innovants à des prix imbattables.</p>
            <div class="main-button">
              <a href="/shop">Acheter Maintenant</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6 subscribe" id="newsletter" style="margin-top: 35px;">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-heading">
            <h6>INFOLETTRE</h6>
            <h2>Soyez les <em>Premiers</em> à Découvrir Nos Nouveautés Exclusives!</h2>
          </div>
          <div class="search-input">
            <form>
              <input type="email" class="form-control" id="subscriberEmail" aria-describedby="emailHelp" placeholder="Votre email...">
              <button type="button" id="subscribeButton">S'abonner</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $sectR->renderAllUnrenderedSections(); ?>
<script>
  $(document).ready(function() {
    <?php
    if (isset($_SESSION['panier']) and $_SESSION['panier'] == "vide") {
    ?>
      toastr.warning('Votre panier est vide!', 'Panier', {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-bottom-right'
      });
    <?php
      unset($_SESSION['panier']);
    }
    ?>
  })
</script>