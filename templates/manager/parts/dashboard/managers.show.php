<!-- Modal pour afficher les détails de l'utilisateur - Version Moderne -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 position-relative p-0">
        <div class="user-header-banner w-100"></div>
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="container-fluid">
          <div class="row">
            <!-- Sidebar avec avatar et infos principales -->
            <div class="col-md-4 bg-light py-4 text-center">
              <div class="position-relative mb-4 mx-auto">
                <div class="avatar-container">
                  <div class="avatar-overlay">
                    <i class="fas fa-user fa-3x text-white"></i>
                  </div>
                </div>
                <span id="userStatusBadge" class="position-absolute badge rounded-pill"></span>
              </div>
              <h3 id="viewUsername" class="fw-bold mb-1"></h3>
              <h6 id="viewFullname" class="text-secondary mb-3"></h6>

              <div class="contact-info mb-3">
                <div class="contact-item">
                  <i class="fas fa-envelope text-primary me-2"></i>
                  <span id="viewEmail" class="text-truncate"></span>
                </div>
                <div class="contact-item mt-2">
                  <i class="fas fa-phone-alt text-primary me-2"></i>
                  <span id="viewPhone" class="text-truncate">-</span>
                </div>
              </div>

              <div class="role-badge my-3" id="roleBadgeContainer">
                <span id="viewRole" class="badge rounded-pill px-3 py-2"></span>
              </div>
            </div>

            <!-- Content principal avec détails -->
            <div class="col-md-8 py-4 px-4">
              <h5 class="border-bottom pb-2 mb-4">
                <i class="fas fa-info-circle me-2 text-primary"></i>Informations
              </h5>

              <div class="row g-4">
                <!-- Carte ID Utilisateur -->
                <div class="col-md-6">
                  <div class="info-card">
                    <div class="info-card-icon">
                      <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="info-card-content">
                      <div class="info-card-label">ID Utilisateur</div>
                      <div class="info-card-value" id="viewUserId"></div>
                    </div>
                  </div>
                </div>

                <!-- Carte Date de création -->
                <div class="col-md-6">
                  <div class="info-card">
                    <div class="info-card-icon">
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-card-content">
                      <div class="info-card-label">Créé depuis</div>
                      <div class="info-card-value" id="viewCreatedAt"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Fermer
        </button>
        <button type="button" class="btn btn-primary rounded-pill manager-edit-btn" id="manager-edit-btn">
          <i class="fas fa-edit me-1"></i>Modifier
        </button>
      </div>
    </div>
  </div>
</div>

<style>#viewUserModal .modal-content {border-radius: 15px;overflow: hidden;}.user-header-banner {height: 80px;background: linear-gradient(135deg, #0ea5e9, #2563eb);position: relative;}/* Avatar */.avatar-container {width: 120px;height: 120px;border-radius: 50%;background: linear-gradient(135deg, #0ea5e9, #2563eb);display: flex;align-items: center;justify-content: center;margin: 0 auto;position: relative;border: 5px solid white;box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);margin-top: -60px;}.avatar-overlay {width: 100%;height: 100%;border-radius: 50%;display: flex;align-items: center;justify-content: center;background-color: rgba(0, 0, 0, 0.1);}/* Badge de statut */#userStatusBadge {bottom: 10px;right: calc(50% - 55px);width: 18px;height: 18px;border: 2px solid white;}/* Badge de rôle */.role-badge .badge {font-size: 0.9rem;letter-spacing: 0.5px;}/* Cartes d'information */.info-card {display: flex;align-items: center;background-color: #f8f9fa;border-radius: 12px;padding: 15px;box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);transition: all 0.3s ease;height: 100%;}.info-card:hover {transform: translateY(-3px);box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);}.info-card-icon {width: 45px;height: 45px;border-radius: 12px;background: linear-gradient(135deg, #0ea5e9, #2563eb);display: flex;align-items: center;justify-content: center;color: white;font-size: 1.2rem;margin-right: 15px;}.info-card-label {font-size: 0.85rem;color: #6c757d;font-weight: 500;}.info-card-value {font-size: 1.1rem;font-weight: 600;color: #212529;}/* Contact info */.contact-info {max-width: 250px;margin: 0 auto;}.contact-item {display: flex;align-items: center;font-size: 0.9rem;}/* Cartes de statistiques */.stat-card {background-color: #fff;border-radius: 12px;padding: 15px;text-align: center;box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);transition: all 0.3s ease;}.stat-card:hover {transform: translateY(-3px);box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);}.stat-value {font-size: 1.8rem;font-weight: 700;color: #2563eb;}.stat-label {font-size: 0.85rem;color: #6c757d;}/* Responsive */@media (max-width: 767.98px) {.avatar-container {margin-top: -40px;width: 100px;height: 100px;}.user-header-banner {height: 60px;}#userStatusBadge {right: calc(50% - 45px);}}</style>