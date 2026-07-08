<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Liste des Produits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal" id="addProductBtnMobile" data-action="add">
                    <i class="fa fa-plus" aria-hidden="true"></i> Nouveau
                </button>
                <!-- Barre de recherche et tri -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un produit...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="sortBy" class="form-select">
                            <option value="created_at">Trier par Date</option>
                            <option value="price">Trier par Prix</option>
                            <option value="status">Trier par Statut</option>
                            <option value="title">Trier par Titre</option>
                            <option value="visited_times">Trier par Visites</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="orderBy" class="form-select">
                            <option value="DESC">Ordre: Descendant</option>
                            <option value="ASC">Ordre: Ascendant</option>
                        </select>
                    </div>
                </div>

                <!-- Indicateur de chargement -->
                <div id="loadingIndicator" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>

                <!-- Tableau des produits -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Titre</th>
                                <th>Prix</th>
                                <th>Visites</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <!-- Les produits seront insérés ici dynamiquement -->
                        </tbody>
                    </table>
                </div>

                <!-- Message d'erreur -->
                <div id="errorMessage" class="alert alert-danger d-none" role="alert"></div>

                <!-- Pagination -->
                <nav aria-label="Navigation des pages de produits">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Les boutons de pagination seront générés ici -->
                    </ul>
                </nav>
            </div>
            <div class="modal-footer">
                <div class="text-muted small">
                    <span id="totalItems">0</span> produits trouvés
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>