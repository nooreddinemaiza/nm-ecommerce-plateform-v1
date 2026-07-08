<div class="modal fade" id="order2sStatsModal" tabindex="-1" aria-labelledby="order2sStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- modal-xl pour une vue plus large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="order2sStatsModalLabel">Liste des Commandes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" id="searchOrders2" class="form-control" placeholder="Rechercher...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterPeriod2">
                                <option value="day">Ce jour</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                                <option value="year">Cette année</option>
                                <option value="custom">Période personnalisée</option>
                            </select>
                        </div>
                        <div class="row g-3 mb-4" id="customDateRange2" style="display: none;">
                            <div class="col-md-4">
                                <input type="date" id="startDate2" class="form-control" placeholder="Date de début">
                            </div>
                            <div class="col-md-4">
                                <input type="date" id="endDate2" class="form-control" placeholder="Date de fin">
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <div id="fiterStat2" class="d-flex flex-wrap gap-2">
                                <label><input type="checkbox" value="pending" class="form-check-input me-1 cmdStat2"> En attente</label>
                                <label><input type="checkbox" value="processing" class="form-check-input me-1 cmdStat2"> En traitement</label>
                                <label><input type="checkbox" value="shipped" class="form-check-input me-1 cmdStat2"> Expédié</label>
                                <label><input type="checkbox" value="delivered" class="form-check-input me-1 cmdStat2"> Livré</label>
                                <label><input type="checkbox" value="cancelled" class="form-check-input me-1 cmdStat2"> Annulé</label>
                                <label><input type="checkbox" value="returned" class="form-check-input me-1 cmdStat2"> Retourné</label>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom Client</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Total (DH)</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody2">
                                <!-- Rempli par AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center bg-light p-3 rounded mt-4 border">
                        <div class="mb-2 mb-md-0">
                            <strong>Total de la période :</strong>
                            <span id="orderStaTotal2" class="ms-2 badge bg-success text-white">0 €</span>
                        </div>
                        <button type="button" class="btn btn-primary" id="printStatsButton2">
                            <i class="fa fa-print me-1"></i> Imprimer
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button id="orderPrevPage2" class="btn btn-outline-primary" disabled>Précédent</button>
                        <span id="paginationInfo2" class="fw-bold"></span>
                        <button id="orderNextPage2" class="btn btn-outline-primary">Suivant</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>