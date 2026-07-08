<?php

use Src\Helpers\Cart;
use Src\Helpers\FileAndPathManager;
use Src\Helpers\UrlHelper;
use Src\Helpers\StringUtils;

$data = Src\Helpers\Helper::safeJsonDecode($mainProduct, "mainProduct");
$data = Src\Helpers\Helper::safeJsonDecode($relatedProducts, "relatedProducts");
$data = Src\Helpers\Helper::safeJsonDecode($data, "csrf_token");

Cart::store(
  [
    'id'        => $mainProduct['id'],
    'title'     => $mainProduct['title'],
    'price'     => $mainProduct['price'],
    'reduction' => $mainProduct['reduction'],
    'appReduction' => $mainProduct['appReduction'],
    'image'     => $mainProduct['images'][array_rand($mainProduct['images'])],
    'category'  => is_array($mainProduct['categories']) ? array_rand($mainProduct['categories']) : $mainProduct['categories'],
    'link'      => UrlHelper::generateProductLink($mainProduct['slug'], (int)$mainProduct['id']),
  ]
);
if (Cart::isProductInCart($mainProduct['id'])) {
  $buttonText = 'Augmenter la quantité';
  $infoMessage = '<p class="small" style="color: #ee626b; margin:0; padding-left:23%;" >Déjà dans votre panier!</p>';
} else {
  $buttonText = 'Ajouter au panier';
  $infoMessage = '';
}
?>
<div class="page-heading header-text" style="background: #0071f8;">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h3><?= htmlspecialchars_decode($mainProduct['title']) ?></h3>
        <span class="breadcrumb"><a href="/">Acceuil</a> > <a href="/shop">Boutique</a> > <?= htmlspecialchars_decode($mainProduct['title']) ?></span>
        <?php if ($mainProduct['reduction'] > 0 && $mainProduct['appReduction'] > 0): ?>
          <script>
            window.productQuantityDiscounts = [{
              min_quantity: <?= $mainProduct['appReduction'] ?>,
              percent: <?= $mainProduct['reduction'] ?>
            }, ];
          </script>
          <div id="quantityDiscountNotification" class="discount-notification" style="display: none;">
            <div class="discount-notification-content">
              <button class="close-notification">&times;</button>
              <h4><i class="fas fa-tag"></i> Offre spéciale !</h4>
              <p id="discountMessage"></p>
            </div>
          </div>
          <style>
            .discount-notification {
              position: fixed;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
              z-index: 1050;
              background: white;
              padding: 20px;
              border-radius: 8px;
              box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
              max-width: 500px;
              width: 90%;
              animation: fadeIn 0.5s;
              border-left: 5px solid #ff6b6b;
            }

            .discount-notification-content {
              position: relative;
            }

            .close-notification {
              position: absolute;
              top: -10px;
              right: -10px;
              background: #ff6b6b;
              color: white;
              border: none;
              width: 30px;
              height: 30px;
              border-radius: 50%;
              font-size: 18px;
              cursor: pointer;
            }

            .discount-notification h4 {
              color: #ff6b6b;
              margin-bottom: 15px;
            }

            .discount-notification p {
              margin-bottom: 0;
            }

            @keyframes fadeIn {
              from {
                opacity: 0;
                transform: translate(-50%, -60%);
              }

              to {
                opacity: 1;
                transform: translate(-50%, -50%);
              }
            }

            .overlay {
              position: fixed;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;
              background: rgba(0, 0, 0, 0.5);
              z-index: 1040;
              display: none;
            }
          </style>
          <script>
            $(document).ready(function() {
              // Vérifier si le produit a des remises par quantité
              function checkQuantityDiscounts() {
                // Récupérer les données des remises depuis votre backend ou variable JS
                const quantityDiscounts = window.productQuantityDiscounts || [];

                if (quantityDiscounts.length > 0) {
                  // Trouver la meilleure remise disponible
                  const bestDiscount = quantityDiscounts.reduce((max, discount) =>
                    discount.percent > max.percent ? discount : max, {
                      percent: 0
                    });

                  if (bestDiscount.percent > 0) {
                    showDiscountNotification(bestDiscount);
                  }
                }
              }

              function showDiscountNotification(discount) {
                const message = `Pour une commande de <strong>${discount.min_quantity}</strong> unités ou plus, 
                  bénéficiez de <strong>${discount.percent}%</strong> de réduction sur ce produit !`;

                $('#discountMessage').html(message);
                $('#quantityDiscountNotification').fadeIn();
                $('<div class="overlay">').insertBefore('#quantityDiscountNotification').fadeIn();
              }

              // Fermer la notification
              $(document).on('click', '.close-notification, .overlay', function() {
                $('#quantityDiscountNotification').fadeOut();
                $('.overlay').fadeOut(function() {
                  $(this).remove();
                });
              });

              // Vérifier au chargement de la page
              checkQuantityDiscounts();

              // Optionnel: aussi vérifier quand la quantité change
              $('input[name="quantity"]').on('change', function() {
                const qty = parseInt($(this).val());
                checkApplicableDiscounts(qty);
              });

              function checkApplicableDiscounts(quantity) {
                const quantityDiscounts = window.productQuantityDiscounts || [];
                const applicable = quantityDiscounts.filter(d => quantity >= d.min_quantity);

                if (applicable.length > 0) {
                  const best = applicable.reduce((max, d) => d.percent > max.percent ? d : max);
                  $('#discountMessage').html(`
          Quantité actuelle: ${quantity}<br>
          Remise appliquée: <strong class="badge">${best.percent}%</strong> (à partir de ${best.min_quantity} unités)
      `);
                  $('#quantityDiscountNotification').fadeIn();
                } else {
                  $('#quantityDiscountNotification').fadeOut();
                }
              }
            });
          </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="single-product section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="left-image">
          <?php
          if (!empty($mainProduct['images'])) {
            if (count($mainProduct['images']) > 0) { ?>
              <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach ($mainProduct['images'] as $index => $image) {
                    $imageFileName = FileAndPathManager::fileExists('product-image', $image) ? $image : 'No_Image_Available.jpg';
                  ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                      <img style="height: 400px;"
                        src="/assets/images/product-image/<?= htmlspecialchars_decode($imageFileName) ?>"
                        class="d-block"
                        alt="<?= htmlspecialchars_decode($mainProduct['title']) ?>">
                    </div>
                  <?php } ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </button>
              </div>
            <?php }
          } else { ?>
            <img style="min-height: 530px;max-height: 540px;"
              src="/assets/images/product-image/unfound.jpg"
              alt="<?= htmlspecialchars_decode($mainProduct['title']) ?>">
          <?php } ?>
        </div>
      </div>

      <div class="col-lg-6 align-self-center">
        <h4><?= htmlspecialchars_decode($mainProduct['title']) ?></h4>
        <span class="price">
          <?php if (($mainProduct['old_price'] ?? 0) > ($mainProduct['price'] ?? 0)) { ?>
            <em><?= htmlspecialchars_decode(($mainProduct['old_price'] ?? 0)) ?></em>
          <?php } ?>
          <?= htmlspecialchars_decode(($mainProduct['price'] ?? 0) > 0 ? $mainProduct['price'] : 0) ?>
        </span>
        <p><?= nl2br(htmlspecialchars_decode($mainProduct['description'])) ?></p>
        <div class="single-product-form" id="qty" action="#">
          <?php if ($mainProduct['reduction'] > 0 && $mainProduct['appReduction'] > 0): ?>
            <p class="text-black">
              <b>-<?= htmlspecialchars_decode($mainProduct['reduction']) ?>%</b> Si vous commandez <?= htmlspecialchars_decode($mainProduct['appReduction']) ?>
            </p>
          <?php endif; ?>
          <?= $infoMessage ?>
          <input type="number" class="form-control" id="quantity" name="quantity"
            aria-describedby="quantity" placeholder="1" min="1" value="1">
          <button id="addToCart" data-id='<?= htmlspecialchars_decode($mainProduct['id']) ?>'><i class="fa fa-shopping-bag"></i> <?= $buttonText ?></button>
        </div>
        <ul>
          <li><span>ID produit:</span> <?= htmlspecialchars_decode($mainProduct['id']) ?></li>
          <li><span>Categories:</span>
            <?php
            $categories = is_array($mainProduct['categories'])
              ? $mainProduct['categories']
              : explode(',', $mainProduct['categories']);

            $categoryLinks = [];
            foreach ($categories as $category) {
              $category = trim($category);
              if (!empty($category)) {
                $link = UrlHelper::generateCategoryLink($category);
                $categoryLinks[] = "<a " . ($category != "Uncategorized" ? "href=\"" . htmlspecialchars_decode($link) : "") . "\">" . htmlspecialchars_decode($category) . "</a>";
              }
            }
            echo implode(', ', $categoryLinks);
            ?>
          </li>
          <?php if (!empty($mainProduct['tag'])) { ?>
            <li>
              <span>Multi-tags:</span>
              <?php
              $tags = is_array($mainProduct['tag'])
                ? $mainProduct['tag']
                : explode(',', $mainProduct['tag']);

              $tags_formated = [];
              foreach ($tags as $tag) {
                $tag = trim(StringUtils::removeHashtags($tag));
                if (!empty($tag)) {
                  $tags_formated[] = htmlspecialchars_decode($tag);
                }
              }
              echo implode(', ', $tags_formated);
              ?>
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
if (count($relatedProducts)) {
?>
  <!-- Section des produits similaires -->
  <div class="section most-played" style="min-height: 450px !important;">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6><?= ($mainProduct['categories']) ?></h6>
            <h2>Produits similaires</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="/shop">Explorer</a>
          </div>
        </div>

        <div class="row trending-prod">
          <?php
          foreach ($relatedProducts as $product) {
            $link = UrlHelper::generateProductLink(
              htmlspecialchars_decode($product['slug']),
              $product['id']
            );

            // Sélectionner une image aléatoire
            $randomImage = $product['images'][0];
            $randomImage = FileAndPathManager::fileExists('product-image', $randomImage) ? $randomImage : 'No_Image_Available.jpg';
          ?>
            <div class="col-lg-3">
              <div class="item">
                <div class="thumb">
                  <a href="<?= $link ?>" class="product-link" data-title="<?= htmlspecialchars($product['title']) ?>">
                    <img src="/assets/images/product-image/<?= htmlspecialchars($randomImage) ?>" alt="" title="<?= htmlspecialchars($product['title']) ?>" style="height: 230px;"></a>
                </div>
                <div class="down-content">
                  <h4 class="card-title">
                    <a href="<?= $link ?>"
                      class="truncate product-title product-link"
                      data-title="<?= htmlspecialchars($product['title']) ?>"
                      style="width: 100% !important;background: none;color: black;text-align: left;border-radius: initial;padding-top: 10px;">
                      <?= htmlspecialchars($product['title']) ?>
                    </a>
                  </h4>
                </div>
              </div>
            </div>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
  </div>
<?php
}
?>