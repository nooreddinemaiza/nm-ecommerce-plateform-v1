
<div class="modal fade" id="subscriberModal" tabindex="-1" aria-labelledby="subscriberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriberModalLabel">Statistiques des Abonnements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Carte Total Abonnés -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-users fa-3x text-primary"></i>
                                </div>
                                <h6 class="card-title text-uppercase text-muted">Total Abonnés</h6>
                                <h2 class="card-text" id="total_subs"><?= $subscribers['total_subs'] ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Abonnés Aujourd'hui -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-plus fa-3x text-success"></i>
                                </div>
                                <h6 class="card-title text-uppercase text-muted">Abonnés Aujourd'hui</h6>
                                <h2 class="card-text" id="today_subs"><?= $subscribers['today_subs'] ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Abonnés ce Mois-ci -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-check fa-3x text-info"></i>
                                </div>
                                <h6 class="card-title text-uppercase text-muted">Abonnés Ce Mois-ci</h6>
                                <h2 class="card-text" id="month_subs"><?= $subscribers['month_subs'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notificationModal">
                        <i class="fas fa-bell me-2"></i>Envoyer une notification
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#noSubsModal">
                        <i class="fas fa-list me-2"></i>
                        Lister les abonnés
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'): ?>
<!-- Modal de notification -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifier les abonnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="subsNotificationMsg" class="form-label">Message de notification</label>
                    <textarea class="form-control" id="subsNotificationMsg" rows="5" placeholder="Saisissez votre message à envoyer aux abonnés..."></textarea>
                </div>
                <div id="notificationStatus" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="sendNotificationBtn" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Notifier tous les abonnés
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>