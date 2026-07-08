<!-- Modal pour afficher les détails du produit -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title" id="viewProductModalLabel">
          <i class="fas fa-box-open me-2"></i><span id="viewProductTitle"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="row g-0">
          <!-- Images du produit - Panneau gauche -->
          <div class="col-md-6 bg-light">
            <div class="position-relative h-100">
              <div id="productImagesCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                <div class="carousel-inner h-100" id="productImagesPreview">
                  <!-- Les images seront injectées ici via JavaScript -->
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Précédent</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Suivant</span>
                </button>
              </div>

              <!-- Indicateur de statut -->
              <div class="position-absolute top-0 end-0 m-3">
                <span id="viewProductStatus" class="badge rounded-pill"></span>
              </div>

              <!-- Compteur de visites -->
              <div class="position-absolute bottom-0 end-0 m-3">
                <span class="badge bg-dark text-white rounded-pill">
                  <i class="fas fa-eye me-1"></i> <span id="viewProductVisites">0</span> visites
                </span>
              </div>
            </div>
          </div>

          <!-- Détails du produit - Panneau droit -->
          <div class="col-md-6">
            <div class="p-4">
              <!-- Prix et réductions -->
              <div class="d-flex align-items-baseline mb-4">
                <h3 id="viewProductPrice" class="text-primary fw-bold mb-0"></h3>
                <del id="viewProductOldPrice" class="text-muted ms-2"></del>
                <span id="viewProductReductionBadge" class="badge bg-danger ms-2"></span>
              </div>

              <!-- Description -->
              <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-2 mb-2">Description</h6>
                <div id="viewProductDescription" class="description-box"></div>
              </div>

              <!-- Informations détaillées -->
              <div class="row mb-4">
                <div class="col-md-6 mb-3">
                  <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-2 mb-2">Informations</h6>
                  <ul class="list-unstyled">
                    <li class="mb-2">
                      <span class="text-muted small">Catégorie :</span>
                      <span id="viewProductCategory" class="ms-1 fw-medium"></span>
                    </li>
                    <li class="mb-2">
                      <span class="text-muted small">Stock :</span>
                      <span id="viewProductStock" class="ms-1 fw-medium"></span>
                    </li>
                    <li class="mb-2">
                      <span class="text-muted small">Slug :</span>
                      <span id="viewProductSlug" class="ms-1 fw-medium"></span>
                    </li>
                  </ul>
                </div>
                <div class="col-md-6 mb-3">
                  <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-2 mb-2">Réductions</h6>
                  <ul class="list-unstyled">
                    <li class="mb-2">
                      <span class="text-muted small">Réduction :</span>
                      <span id="viewProductReduction" class="ms-1 fw-medium"></span>
                    </li>
                    <li class="mb-2">
                      <span class="text-muted small">Réduction appliquée :</span>
                      <span id="viewProductAppReduction" class="ms-1 fw-medium"></span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Mots-clés et SEO -->
              <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-2 mb-2">SEO et Tags</h6>
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small">Tags :</span>
                    <span id="viewProductTag" class="ms-1 fw-medium"></span>
                  </div>
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small">Meta Tags :</span>
                    <span id="viewProductMetaTag" class="ms-1 fw-medium"></span>
                  </div>
                  <div class="col-12">
                    <span class="text-muted small">Meta Description :</span>
                    <div id="viewProductMetaDescription" class="mt-1 small"></div>
                  </div>
                </div>
              </div>

              <!-- Créateur -->
              <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                <i class="fas fa-user-edit me-2 text-muted"></i>
                <div>
                  <span class="text-muted small">Créé par</span>
                  <div id="viewProductCreator" class="fw-medium"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-top d-flex justify-content-between">
        <div>
          <button class="btn btn-warning btn-sm modify-product" id="modify-product" title="Modifier le produit">
            <i class="fa-solid fa-pen-to-square me-1"></i> Modifier
          </button>
          <button class="btn btn-danger btn-sm delte-product2" id="delete-product-btn" title="Supprimer le produit">
            <i class="fa-solid fa-trash me-1"></i> Supprimer
          </button>
        </div>
        <div>
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Fermer</button>
          <a class="btn btn-success" href="#" id="viewProductLink" title="Visiter la page du produit" target="_blank">
            <i class="fa-solid fa-square-arrow-up-right me-1"></i> Voir
          </a>
        </div>
      </div>
    </div>
  </div>
</div>