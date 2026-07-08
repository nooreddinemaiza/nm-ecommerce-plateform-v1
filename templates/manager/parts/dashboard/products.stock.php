<!-- Modal amélioré avec structure optimisée -->
<div class="modal fade" id="observeStockModal" tabindex="-1" aria-labelledby="observeStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="observeStockModalLabel">Gestion du stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Barre d'outils avec recherche et filtres -->
        <div class="row mb-3 g-3">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" id="stockSearchInput" placeholder="Rechercher par titre ou ID...">
              <button class="btn btn-outline-secondary" type="button" id="stockClearSearchBtn">Effacer</button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" id="stockStatusFilter">
              <option value="all">Tous les statuts</option>
              <option value="in_stock">En stock</option>
              <option value="out_of_stock">Pas en stock</option>
              <option value="low_stock">Stock faible (< 5)</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" id="rowsPerPage">
              <option value="10">10 lignes/page</option>
              <option value="25" selected>25 lignes/page</option>
              <option value="50">50 lignes/page</option>
              <option value="100">100 lignes/page</option>
            </select>
          </div>
          <div class="col-md-2 d-flex justify-content-end">
            <button class="btn btn-outline-primary" id="printStockBtn">
              <i class="fas fa-print me-1"></i> Imprimer
            </button>
          </div>
        </div>
        <!-- Tableau de stock -->
        <div class="table-responsive" style="max-height: 60VH;">
          <table class="table table-striped table-hover align-middle" id="stockTable">
            <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>En stock</th>
                <th>Livré</th>
                <th>Statut</th>
                <th>Dernière mise à jour</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="stockTableBody">
              <!-- Les données du produit seront insérées ici dynamiquement -->
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="row mt-3">
          <div class="col-md-6">
            <div class="d-flex align-items-center">
              <span class="me-2">Page:</span>
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="stockPagination">
                  <!-- La pagination sera générée ici dynamiquement -->
                </ul>
              </nav>
            </div>
          </div>
          <div class="col-md-6 text-end">
            <span id="paginationInfo" class="text-muted"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Template pour l'impression (caché) -->
<div id="printTemplate" style="display:none;">
  <h3 class="text-center">Rapport de stock - <span id="printDate"></span></h3>
  <table class="table table-bordered" id="printTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Titre</th>
        <th>En stock</th>
        <th>Livré</th>
        <th>Statut</th>
      </tr>
    </thead>
    <tbody id="printTableBody"></tbody>
  </table>
  <div class="text-end mt-3">
    <p>Total produits: <span id="printTotalProducts"></span></p>
    <p>En stock: <span id="printInStock"></span></p>
    <p>Pas en stock: <span id="printOutOfStock"></span></p>
  </div>
</div>