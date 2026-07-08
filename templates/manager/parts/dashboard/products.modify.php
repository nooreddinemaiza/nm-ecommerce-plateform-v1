<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editProductModalLabel">Modifier un Produit</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- Onglets de navigation avec Font Awesome -->
        <ul class="nav nav-tabs mb-4" id="productEditTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
              type="button" role="tab" aria-controls="general" aria-selected="true">
              <i class="fas fa-info-circle me-1"></i> Général
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing"
              type="button" role="tab" aria-controls="pricing" aria-selected="false">
              <i class="fas fa-dollar-sign me-1"></i> Prix
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="description-tab" data-bs-toggle="tab" data-bs-target="#description"
              type="button" role="tab" aria-controls="description" aria-selected="false">
              <i class="fas fa-align-left me-1"></i> Description
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories"
              type="button" role="tab" aria-controls="categories" aria-selected="false">
              <i class="fas fa-tags me-1"></i> Catégories
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images"
              type="button" role="tab" aria-controls="images" aria-selected="false">
              <i class="fas fa-images me-1"></i> Images
            </button>
          </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content p-2" id="productEditTabContent">
          <!-- Onglet Général -->
          <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
            <div class="row mb-3">
              <div class="col-md-7">
                <label for="editProductName" class="form-label">Titre du produit</label>
                <input type="text" class="form-control man-title" id="editProductName" placeholder="Ex: T-shirt en coton" required>
              </div>
              <div class="col-md-5">
                <label for="editProductSlug" class="form-label">URL du produit</label>
                <div class="input-group">
                  <span class="input-group-text text-muted">/shop/</span>
                  <input type="text" class="form-control man-slug-input" id="editProductSlug">
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editProductStock" class="form-label">Stock disponible</label>
                <input type="number" class="form-control" id="editProductStock" placeholder="Ex: 50">
              </div>

              <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_manager'): ?>
                <div class="col-md-6">
                  <label class="form-label">Statut</label>
                  <div class="d-flex gap-3 mt-2">
                    <div class="form-check">
                      <input type="radio" class="form-check-input edit-product-status reduit" name="editProductStatus" id="statusReduit" value="reduit">
                      <label class="form-check-label" for="statusReduit">Réduit</label>
                    </div>
                    <div class="form-check">
                      <input type="radio" class="form-check-input edit-product-status affiche" name="editProductStatus" id="statusAffiche" value="affiche">
                      <label class="form-check-label" for="statusAffiche">Visible</label>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Onglet Prix -->
          <div class="tab-pane fade" id="pricing" role="tabpanel" aria-labelledby="pricing-tab">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editProductPrice" class="form-label">Prix (DH)</label>
                <div class="input-group">
                  <input type="number" class="form-control" id="editProductPrice" placeholder="Ex: 19.99" required>
                  <span class="input-group-text">DH</span>
                </div>
              </div>

              <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_manager'): ?>
                <div class="col-md-6">
                  <label for="editproductReduction" class="form-label">Réduction</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="editproductReduction" placeholder="Ex: 50">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editproductReductionThreshold" class="form-label">Réduction à partir de</label>
                <div class="input-group">
                  <input type="number" class="form-control" id="editproductReductionThreshold" placeholder="Ex: 5">
                  <span class="input-group-text">produits</span>
                </div>
              </div>
            <?php endif; ?>
            </div>
          </div>

          <!-- Onglet Description -->
          <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab">
            <div class="mb-3">
              <label for="editProductDescription" class="form-label">Description du produit</label>
              <textarea class="form-control" id="editProductDescription" rows="4" placeholder="Ex: Un t-shirt confortable et élégant, parfait pour toutes les occasions." required></textarea>
            </div>

            <div class="mb-3">
              <label for="editMetaProductDescription" class="form-label">Meta Description</label>
              <textarea class="form-control" id="editMetaProductDescription" rows="3" placeholder="Ex: Un t-shirt confortable et élégant, parfait pour toutes les occasions." required></textarea>
              <small class="form-text text-muted">Important pour le référencement, décrivez brièvement votre produit (150-160 caractères).</small>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editProductTags" class="form-label">Tags</label>
                <textarea class="form-control meta_tags_input" id="editProductTags" rows="2" placeholder="Ex: t-shirt, coton, été"></textarea>
              </div>
              <div class="col-md-6">
                <label for="editMetaProductTags" class="form-label">Meta Tags</label>
                <textarea class="form-control meta_tags_input" id="editMetaProductTags" rows="2" placeholder="Ex: t-shirt, coton, été"></textarea>
              </div>
              <div class="col-12 mt-2">
                <<div class="alert alert-info py-2 small">
                  <i class="fas fa-info-circle me-1"></i>
                  <strong>Conseils :</strong> Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou <strong>virgule</strong> pour valider chaque tag.
              </div>
            </div>
          </div>
        </div>

        <!-- Onglet Catégories -->
        <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
          <div class="mb-4">
            <label class="form-label mb-3">Sélectionnez les catégories</label>
            <div class="d-flex flex-wrap gap-2 border p-3 rounded" id="editProductCategories">
              <!-- Les catégories seront injectées ici via JavaScript -->
            </div>
          </div>
        </div>

        <!-- Onglet Images -->
        <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
          <div class="mb-3">
            <label class="form-label">Images actuelles</label>
            <div class="row g-2" id="editProductImagesPreview">
              <!-- Les images existantes seront injectées ici via JavaScript -->
            </div>
          </div>

          <div class="mb-3">
            <label for="editProductImageInput" class="form-label">Ajouter de nouvelles images</label>
            <div class="input-group mb-2">
              <input type="file" class="form-control" id="editProductImageInput" accept="image/*" multiple>
              <button class="btn btn-outline-secondary" type="button" id="uploadImagesBtn">
                <i class="fas fa-upload"></i>
              </button>
            </div>
            <div class="alert alert-light border py-2 small">
              <i class="fas fa-info-circle me-1"></i>
              <strong>Note :</strong> Formats acceptés : JPEG, PNG. Taille maximale : 5 Mo par image. Maximum 4 images.
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer bg-light">
      <div class="d-flex w-100 justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="$('#manageProductsModal').modal('show')">
          <i class="fas fa-times-circle me-1"></i> Annuler
        </button>
        <button type="submit" id="updateProduct" class="btn btn-primary">
           <i class="fas fa-plus-circle me-1"></i> Enregistrer les modifications
        </button>
      </div>
    </div>
  </div>
</div>
</div>
