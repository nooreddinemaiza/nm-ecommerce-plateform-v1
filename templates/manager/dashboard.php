<?php

use Src\Helpers\Config;
use Src\Helpers\FileAndPathManager;

function parts($name, $data)
{
  FileAndPathManager::includeFile('manager-part', "dashboard/$name", $data);
}
$data = Src\Helpers\Helper::safeJsonDecode($data, "categories");
$data = Src\Helpers\Helper::safeJsonDecode($data, "users");
$data = Src\Helpers\Helper::safeJsonDecode($data, "products");
$data = Src\Helpers\Helper::safeJsonDecode($data, "pages");

parts('custom.toast.php', []);
parts('navbar.php', $data);
?>

<script>
  const csrf_token = '<?= $csrf_token ?>';
  const ORDER_ID_NUMBER = <?= Config::get("ORDER_ID_NUMBER") ?>;
</script>
<div id="collapseGroup" class="col-lg-9 col-xl-10 ms-auto">
  <?php
  if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'):

    //pages
    parts('pages.manage.php', [
      "pages" => $pages,
      "products" => $data['products'],
      "categories" => $data['categories']
    ]);

    parts('articles.manage.php', $data);

    parts('custom.sections.php', []);
    // Managers Modals
    parts('managers.manage.php', $data);
    parts('managers.new.php', $data);
    parts('managers.show.php', $data);
    parts('managers.status.php', $data);
    parts('products.stock.php', $data);

    // categories Modals
    parts('categories.manage.php', $data);
    parts('categories.new.php', $data);
    parts('categories.edit.php', $data);
    parts('categories.delete.php', $data);
    parts('categories.edit.products.php', $data);
    parts('categories.show.php', $data);
  endif;
  // profile Modals
  parts('profile.modify.php', []);
  // products Modals
  parts('products.new.php', $data);
  parts('products.manage.php', $data);
  parts('products.modify.php', $data);
  parts('products.show.php', $data);

  // orders Modals
  parts('orders.show.php', $data);
  parts('orders.stats2.php', $data);

  // feedback Modals
  parts('feedback.manage.php', $data);
  parts('subscribers.show.php', $data);
  parts('subscribers.list.php', $data);
  ?>
  <?php
  if (!empty($data['productsChart'])) {
    parts('products.chart.php', $data);
  }
  ?>
  <!-- Main content container -->
  <div class="container-fluid py-3" id="mainContainerp">
    <!-- First Row - Main Stats -->
    <div class="row g-3 mb-3">
      <!-- Most Popular Products Card -->
      <?php if (!empty($data['productsChart'])): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm hover-card" data-bs-toggle="modal" data-bs-target="#ordersChart">
            <div class="card-body d-flex align-items-center">
              <div class="bg-primary bg-gradient rounded-circle p-3 text-white me-3">
                <i class="fas fa-chart-line fa-lg"></i>
              </div>
              <div>
                <h6 class="text-muted mb-1 small text-uppercase">Produits populaires</h6>
                <h5 class="mb-0" id="most-ordered">
                  <span class="fw-bold"></span>
                  <small class="text-success ms-1"><i class="fas fa-arrow-up"></i></small>
                </h5>
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
              <small class="text-primary d-flex justify-content-between align-items-center">
                <span><i class="fas fa-eye me-1"></i>Voir les détails</span>
                <i class="fas fa-chevron-right"></i>
              </small>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- New Orders Card -->
      <?php if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm hover-card">
            <?php parts('orders.new.php', $data); ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Reduced Products Card -->
      <?php
      $unvisible_products = 0;
      $vsproducts = is_string($products) ? json_decode($products, true) : $products;
      foreach ($vsproducts as $product) {
        if ($product['status'] == 'reduit') $unvisible_products++;
      }
      if ($unvisible_products > 0):
      ?>
        <div class="col-md-6 col-lg-3" id="unvisibleProductsWidget">
          <div class="card h-100 border-0 shadow-sm hover-card" id="unvisibleProducts" data-bs-toggle="modal" data-bs-target="#productModal">
            <div class="card-body d-flex align-items-center">
              <div class="bg-warning bg-gradient rounded-circle p-3 text-white me-3">
                <i class="fas fa-eye-slash fa-lg"></i>
              </div>
              <div>
                <h6 class="text-muted mb-1 small text-uppercase">Produits réduits</h6>
                <h5 class="mb-0" id="unvisibleProductsCount">
                  <span class="fw-bold"><?= $unvisible_products ?></span>
                  <small class="text-muted ms-1">produits</small>
                </h5>
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
              <small class="text-primary d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-1"></i>Voir la liste</span>
                <i class="fas fa-chevron-right"></i>
              </small>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Latest Product Card -->
      <?php
      $crAt = 0;
      $latest_product = '';
      $vsproducts = is_string($products) ? json_decode($products, true) : $products;
      foreach ($vsproducts as $product) {
        if ($product['created_at'] > $crAt) {
          $crAt = $product['created_at'];
          $latest_product = $product;
        }
      }
      if (!empty($latest_product)):
      ?>
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm hover-card" id="latestProducts" data-bs-toggle="modal" data-bs-target="#productModal">
            <div class="card-body d-flex align-items-center">
              <div class="bg-success bg-gradient rounded-circle p-3 text-white me-3">
                <i class="fas fa-clock fa-lg"></i>
              </div>
              <div>
                <h6 class="text-muted mb-1 small text-uppercase">Dernier produit</h6>
                <div class="text-truncate fw-bold" style="max-width: 160px;"><?= $latest_product['title'] ?></div>
                <small class="text-muted">Par <?= $latest_product['creator'] ?></small>
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
              <small class="text-primary d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-1"></i>Voir le produit</span>
                <i class="fas fa-chevron-right"></i>
              </small>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Second Row - Additional Info -->
    <div class="row g-3">
      <!-- Recent Feedback -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-comments text-primary me-2"></i>Feedback récent</h6>
            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#feedbacksModal">
              Tout voir <i class="fas fa-arrow-right ms-1"></i>
            </button>
          </div>
          <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
            <?php parts('feedback.new.php', $data); ?>
          </div>
        </div>
      </div>

      <!-- Subscribers -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-transparent border-0">
            <h6 class="mb-0"><i class="fas fa-user-plus text-success me-2"></i>Abonnés</h6>
          </div>
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-3">
              <div class="position-relative">
                <div class="rounded-circle bg-light" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                  <div>
                    <h3 class="mb-0 fw-bold text-success"><?= $subscribers['today_subs'] ?></h3>
                    <small class="text-muted">aujourd'hui</small>
                  </div>
                </div>
                <div class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <i class="fas fa-bell"></i>
                </div>
              </div>
            </div>
            <button class="btn btn-sm btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#subscriberModal">
              <i class="fas fa-users me-2"></i>Voir tous les abonnés
            </button>
          </div>
        </div>
      </div>

      <!-- Most Visited Product -->
      <?php
      if (!empty($products)):
        $imagepath = '';
        $title = '';
        $visits = 0;
        $id = '';
        foreach (json_decode($products, true) as $product) {
          if ($product['visited'] > $visits) {
            if (is_array($product['images'])) {
              list($imgId, $path) = explode('|', $product['images'][0], 2);
            } else {
              list($imgId, $path) = explode('|', $product['images'], 2);
            }
            $id = $product['id'];
            $imagepath = $path;
            $title = $product['title'];
            $visits = $product['visited'];
          }
        }
      ?>
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
              <h6 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Produit le plus visité</h6>
            </div>
            <div class="card-body text-center">
              <div class="row align-items-center">
                <div class="col-5">
                  <img src="/assets/images/product-image/<?= !empty($imagepath) ? $imagepath : 'No_Image_Available.jpg' ?>"
                    alt="<?= $title ?>" class="img-fluid rounded shadow-sm" style="max-height: 100px; object-fit: cover;">
                </div>
                <div class="col-7 text-start">
                  <h6 class="mb-1 text-truncate" title="<?= $title ?>"><?= $title ?></h6>
                  <div class="text-muted mb-2">
                    <i class="fas fa-eye text-primary me-1"></i>
                    <span class="visits-count"><?= $visits ?></span> visites
                  </div>
                  <button class="btn btn-sm btn-outline-primary rounded-pill product-view-details" data-id="<?= $id ?>">
                    <i class="fas fa-search me-1"></i>Détails
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>