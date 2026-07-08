<!-- Orders Statistics Card -->
<div class="card h-100 shadow-sm" data-bs-toggle="modal" href="#order2sStatsModal" role="button" aria-expanded="false" aria-controls="ordersStatsModal">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-bottom-0 py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-chart-bar me-2"></i>Statistiques Commandes
        </h6>
    </div>

    <!-- Card Body -->
    <div class="card-body pt-0">
        <div class="orders-stats-grid">
            <div class="stat-item">
                <span class="stat-label">Total</span>
                <span class="stat-value stat-total"><?= $ordersStats['total']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Aujourd'hui</span>
                <span class="stat-value stat-today"><?= $ordersStats['today']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Semaine</span>
                <span class="stat-value stat-week"><?= $ordersStats['week']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Mois</span>
                <span class="stat-value stat-month"><?= $ordersStats['month']; ?></span>
            </div>
        </div>
    </div>

    <!-- Card Footer -->
    <div class="card-footer bg-transparent text-center border-top">
        <span class="text-primary">Voir les commandes</span>
    </div>

</div>