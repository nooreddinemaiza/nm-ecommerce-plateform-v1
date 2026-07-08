<div class="modal fade shcategory-modal" id="shcategoryDetailsModal" tabindex="-1" aria-labelledby="shcategoryDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shcategoryDetailsModalLabel">
          <i class="fas fa-tag me-2"></i>Détails de la catégorie
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5">
            <img src="" alt="Image de la catégorie" class="shcategory-image" id="shcategoryImage">
            <div class="shcategory-meta">
              <i class="far fa-calendar-alt"></i> Crée le:
              <span id="shcategoryDate"></span>
            </div>
            <div id="shcategoryTagsContainer">
            </div>
          </div>
          <div class="col-md-7">
            <h3 id="shcategoryTitle" class="mb-3"></h3>
            <div class="d-flex mb-3">
              <div class="me-2" id="trendBadgeContainer">
              </div>
              <div id="reductionBadgeContainer">
              </div>
            </div>
            <div class="mb-3">
              <h5><i class="fas fa-info-circle me-2"></i>Description</h5>
              <p id="shcategoryDescription" class="text-muted"></p>
            </div>
            <h5 class="shcategory-link">
              <i class="fas fa-link me-1"></i>
              <a href="#" id="shcategoryLink" target="_blank"></a>
            </h5>
            <div class="shcategory-stats">
              <div class="cat-stat-item">
                <i class="fas fa-box fa-2x mb-2 text-primary"></i>
                <h5 id="productCount"></h5>
              </div>
              <div class="cat-stat-item">
                <i class="fas fa-eye fa-2x mb-2 text-info"></i>
                <h5 id="visitesCount"></h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div>
          <span class="text-muted me-2">ID: <span id="shcategoryId"></span></span>
        </div>
        <div>
          <button type="button" class="btn btn-outline-success btn-action get-category-pr" id="get-category-pr" data-category-id="" data-bs-toggle="modal" data-bs-target="#catproductCModal">
            <i class="fa fa-plus" aria-hidden="true"></i> Produits
          </button>
          <button type="button" class="btn btn-outline-danger btn-action delete-category-btn" id="delete-category-btn" data-category-id="">
            <i class="fas fa-trash-alt me-1"></i> Supprimer
          </button>
          <button type="button" class="btn btn-primary btn-action edit-category-btn" id="edit-category-btn" data-category-id="">
            <i class="fas fa-edit me-1"></i> Modifier
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fermer</button>
        </div>
      </div>
    </div>
  </div>
</div>