<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addProductModalLabel">Ajouter un Produit</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- Navigation à onglets -->
        <ul class="nav nav-tabs mb-4" id="productAddTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="add-general-tab" data-bs-toggle="tab" data-bs-target="#add-general"
              type="button" role="tab" aria-controls="add-general" aria-selected="true">
              <i class="fas fa-info-circle me-1"></i> Général
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-pricing-tab" data-bs-toggle="tab" data-bs-target="#add-pricing"
              type="button" role="tab" aria-controls="add-pricing" aria-selected="false">
              <i class="fas fa-tags me-1"></i> Prix
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-description-tab" data-bs-toggle="tab" data-bs-target="#add-description"
              type="button" role="tab" aria-controls="add-description" aria-selected="false">
              <i class="fas fa-align-left me-1"></i> Description
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-categories-tab" data-bs-toggle="tab" data-bs-target="#add-categories"
              type="button" role="tab" aria-controls="add-categories" aria-selected="false">
              <i class="fas fa-sitemap me-1"></i> Catégories
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-images-tab" data-bs-toggle="tab" data-bs-target="#add-images"
              type="button" role="tab" aria-controls="add-images" aria-selected="false">
              <i class="fas fa-images me-1"></i> Images
            </button>
          </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content p-2" id="productAddTabContent">
          <!-- Onglet Général -->
          <div class="tab-pane fade show active" id="add-general" role="tabpanel" aria-labelledby="add-general-tab">
            <div class="row mb-3">
              <div class="col-md-7">
                <label for="productName" class="form-label">Titre du produit</label>
                <input type="text" class="form-control man-title" id="productName" placeholder="Ex: Produit 10">
                <small class="text-muted"><i class="fas fa-exclamation-circle"></i> Le titre ne doit pas contenir que des caractères et nombres!</small>
              </div>
              <div class="col-md-5 man-slug">
                <label for="productSlug" class="form-label">URL du produit</label>
                <div class="input-group">
                  <span class="input-group-text text-muted">/shop/</span>
                  <input type="text" class="form-control man-slug-input" id="productSlug">
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="productStock" class="form-label">Stock disponible</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                  <input type="number" class="form-control" id="productStock" placeholder="Ex: 50">
                </div>
              </div>
            </div>
          </div>

          <!-- Onglet Prix -->
          <div class="tab-pane fade" id="add-pricing" role="tabpanel" aria-labelledby="add-pricing-tab">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="productPrice" class="form-label">Prix</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                  <input type="number" class="form-control" id="productPrice" placeholder="Ex: 19.99">
                  <span class="input-group-text">DH</span>
                </div>
              </div>

              <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_manager'): ?>
                <div class="col-md-6">
                  <label for="productReduction" class="form-label">Réduction</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                    <input type="number" class="form-control" id="productReduction" placeholder="Ex: 50">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="productReductionThreshold" class="form-label">Réduction à partir de</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-sort-numeric-up"></i></span>
                  <input type="number" class="form-control" id="productReductionThreshold" placeholder="Ex: 5">
                  <span class="input-group-text">produits</span>
                </div>
              </div>
            <?php endif; ?>
            </div>
          </div>

          <!-- Onglet Description -->
          <div class="tab-pane fade" id="add-description" role="tabpanel" aria-labelledby="add-description-tab">
            <div class="mb-3">
              <label for="productDescription" class="form-label">Description du produit</label>
              <textarea class="form-control" id="productDescription" rows="4" placeholder="Ex: Un t-shirt confortable et élégant, parfait pour toutes les occasions." required></textarea>
            </div>

            <div class="mb-3">
              <label for="metaDescription" class="form-label">Meta Description</label>
              <textarea class="form-control" id="metaDescription" rows="3" placeholder="Ex: Un t-shirt confortable et élégant, parfait pour toutes les occasions." required></textarea>
              <small class="form-text text-muted"><i class="fas fa-search"></i> Important pour le référencement, décrivez brièvement votre produit (150-160 caractères).</small>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="productTags" class="form-label">Tags</label>
                <textarea class="form-control meta_tags_input" id="productTags" rows="2" placeholder="Ex: t-shirt, coton, été"></textarea>
              </div>
              <div class="col-md-6">
                <label for="metaProductTags" class="form-label">Meta Tags</label>
                <textarea class="form-control meta_tags_input" id="metaProductTags" rows="2" placeholder="Ex: t-shirt, coton, été"></textarea>
              </div>
              <div class="col-12 mt-2">
                <div class="alert alert-info py-2 small">
                  <i class="fas fa-info-circle me-1"></i>
                  <strong>Conseils :</strong> Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l'intérieur d'un tag.
                </div>
              </div>
            </div>
          </div>

          <!-- Onglet Catégories -->
          <div class="tab-pane fade" id="add-categories" role="tabpanel" aria-labelledby="add-categories-tab">
            <div class="mb-4">
              <label class="form-label mb-3">Sélectionnez les catégories</label>
              <div class="d-flex flex-wrap gap-2 border p-3 rounded" id="addProductModalCatego">
                <!-- Les catégories seront injectées ici via JavaScript -->
              </div>
            </div>
          </div>

          <!-- Onglet Images -->
          <div class="tab-pane fade" id="add-images" role="tabpanel" aria-labelledby="add-images-tab">
            <div class="mb-3">
              <label for="newproductImages" class="form-label">Images du produit</label>
              <div class="input-group mb-2">
                <span class="input-group-text"><i class="fas fa-file-image"></i></span>
                <input type="file" class="form-control" id="newproductImages" accept="image/*" multiple required>
                <button class="btn btn-outline-secondary" type="button" id="clearImagesBtn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="alert alert-light border py-2 small">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Note :</strong> Formats acceptés : JPEG, PNG. Taille maximale : 5 Mo par image. Maximum 4 images.
              </div>

              <div id="imagePreview" class="mt-3 d-flex flex-wrap gap-2">
                <!-- Les aperçus d'images seront injectés ici via JavaScript -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <div class="d-flex w-100 justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times-circle me-1"></i> Annuler
          </button>
          <button type="submit" id="add-new-product" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Ajouter le produit
          </button>
        </div>
      </div>
    </div>
  </div>
</div>