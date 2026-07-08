<!-- Modal pour la gestion des produits -->
<div class="modal fade" id="catproductCModal" tabindex="-1" aria-labelledby="catproductCModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="catproductCModalLabel"><i class="fas fa-box"></i> Modification des produits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Sélectionnez les produits à modifier et cliquez sur "Enregistrer les modifications"
                </div>

                <!-- Filtres et recherche -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="productCSearch" placeholder="Rechercher un produit...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="all">Tous les statuts</option>
                            <option value="affiche">Affiché</option>
                            <option value="reduit">Réduit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="sortProductCs">
                            <option value="title-asc">Nom (A-Z)</option>
                            <option value="title-desc">Nom (Z-A)</option>
                            <option value="price-asc">Prix (croissant)</option>
                            <option value="price-desc">Prix (décroissant)</option>
                            <option value="cat-first">Avec catégorie d'abord</option>
                            <option value="no-cat-first">Sans catégorie d'abord</option>
                        </select>
                    </div>
                </div>

                <!-- En-tête avec sélection multiple -->
                <div class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllProductCs">
                        <label class="form-check-label" for="selectAllProductCs">
                            <strong>Tout sélectionner</strong>
                        </label>
                    </div>
                    <div class="d-flex">
                        <span class="badge bg-success me-2" id="countSelected">0 sélectionné(s)</span>
                        <span class="badge bg-danger me-2" id="countDeselected">0 désélectionné(s)</span>
                        <span class="badge bg-primary" id="totalProductCs">0 total</span>
                    </div>
                </div>

                <!-- Liste des produits -->
                <div class="productC-list" id="productCListContainer" style="max-height: 80vh;overflow: auto;">
                    <!-- Les produits seront chargés ici dynamiquement -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des produits...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row w-100">
                    <div class="col-12 mb-2">
                        <div class="d-flex justify-content-between">
                            <span id="selectionSummary" class="small fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-primary" id="saveChangesBtn">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .productC-item {
        transition: all 0.2s ease;
    }

    .productC-item:hover {
        background-color: #f8f9fa;
    }

    .productC-checkbox:checked+label {
        font-weight: bold;
    }

    .productC-item.with-category {
        border-left: 4px solid #198754 !important;
    }

    .productC-item.no-category {
        border-left: 4px solid #dc3545 !important;
    }

    .productC-item.already-associated {
        background-color: #e8f4f8;
    }

    .category-tag {
        display: inline-block;
        padding: 2px 6px;
        margin: 1px;
        border-radius: 3px;
        font-size: 85%;
        color: white;
        background-color: #6c757d;
    }

    .category-tag.current {
        background-color: #0d6efd;
        font-weight: bold;
    }

    .checkbox-disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    #bulkActionContainer {
        transition: all 0.3s ease;
    }

    .btn-group .btn {
        margin-right: 2px;
    }
</style>

