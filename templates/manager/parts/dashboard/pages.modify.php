
    <!-- Home page -->
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
                  <label for="homeMetaKeys" class="form-label">Keywords</label>
                  <div class="input-group">
                    <textarea class="form-control" id="homeMetaKeys"><?= trim($home['page_meta_keywords']) ?></textarea>
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
                <?php
                // Récupérer la bannière
                $banner = !empty($home['page_data']) ? json_decode($home['page_data'], true) : ["titre1" => "", "titre2" => "", "description" => ""];
                ?>
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
                  <div class="row g-3">
                    <?php foreach ($data['products'] as $product): ?>
                      <?php if ($product['status'] === "affiche"): ?>
                        <div class="col-6 col-md-3">
                          <div class="product-box card selectable-product <?= in_array($product['id'], $selectedProductIds) ? 'selected' : '' ?>" data-product-id="<?= htmlspecialchars($product['id']) ?>" style="max-height: 180px;">
                            <?php
                            $image = is_array($product['images']) ? ($product['images'][array_rand($product['images'])] ?? 'No_Image_Available.jpg') : ($product['images'] ?? 'No_Image_Available.jpg');
                            $imageParts = explode('|', $image);
                            $imageFileName = $imageParts[1] ?? $image;
                            ?>
                            <img src="/assets/images/product-image/<?= htmlspecialchars($imageFileName) ?>" class="card-img-top" alt="Image du produit">
                            <div class="card-body text-center">
                              <h6 class="card-title"><?= htmlspecialchars($product['title']) ?></h6>
                            </div>
                          </div>
                        </div>

                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>

              <!-- Boutons du formulaire -->
              <div class="card-footer">
                <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">Fermer</button>
                <button type="submit" id="trendingSub" class="btn btn-primary" disabled>Modifier</button>
              </div>
            </div>
          </div>
          <!-- Trending categories part -->
          <div class="tab-pane fade" id="top-categories-content" role="tabpanel" aria-labelledby="top-categories-tab">

            <div class="card shadow-sm">
              <div class="card-header bg-gradient">
                <h5 class="card-title mb-0 text-white">Trending Categories</h5>
              </div>
              <div class="card-body" style="max-height: 480px; overflow-y: auto;">
                <!-- Barre de recherche -->
                <div class="search-container mb-4 position-relative">
                  <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                  <input type="text" id="categorySearch" class="form-control form-control-lg ps-5" placeholder="Rechercher une catégorie...">
                </div>

                <!-- Liste des catégories -->
                <div class="category-dropdown">
                  <ul id="categoryList" class="list-group shadow-sm d-none">
                    <?php
                    $trend_cats = [];
                    if (is_array($data['categories']) && !empty($data['categories'])):
                      foreach ($data['categories'] as $category):
                        if ($category['is_trend']) $trend_cats[] = $category;
                    ?>
                        <li class="list-group-item list-group-item-action d-flex align-items-center" data-info='<?= json_encode(['id' => $category['id'], 'title' => $category['title']]) ?>'>
                          <span class="category-dot me-2"></span>
                          <?= htmlspecialchars($category['title']) ?>
                          <i class="fas fa-plus ms-auto text-primary"></i>
                        </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      Pas de categories trouvée!
                    <?php endif; ?>
                  </ul>
                </div>

                <!-- Catégories sélectionnées -->
                <div id="selectedCategories" class="selected-categories-grid">
                  <?php foreach ($trend_cats as $trend): ?>
                    <div class="category-card fade-in" data-id="<?= $trend['id'] ?>">
                      <div class="category-content">
                        <span class="category-title"><?= htmlspecialchars($trend['title']) ?></span>
                        <button type="button" class="btn-close remove-category" data-id="<?= $trend['id'] ?>">
                        </button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Boutons de contrôle -->
              <div class="card-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#homeManage">
                  <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="submit" id="trendingCats" class="btn btn-primary">
                  <i class="fas fa-save me-2"></i>Enregistrer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Shop page: -->
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
                  <label for="shopMetaKeys" class="form-label">Keywords</label>
                  <div class="input-group">
                    <textarea class="form-control" id="shopMetaKeys"><?= trim($shop['page_meta_keywords']) ?></textarea>
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
    <!-- Contact page: -->
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
                  <label for="contactMetaKeys" class="form-label">Keywords</label>
                  <div class="input-group">
                    <textarea class="form-control" id="contactMetaKeys"><?= trim($contact['page_meta_keywords']) ?></textarea>
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