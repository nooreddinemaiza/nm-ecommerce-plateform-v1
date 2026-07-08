<!-- Modal de modification du profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg animate__animated animate__fadeInDown">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title" id="editUserModalLabel">
          <i class="fas fa-user-edit me-2 pulse"></i>Modification du Profil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <div class="avatar-upload">
            <div class="avatar-preview rounded-circle mx-auto" style="width: 100px; height: 100px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-user-circle fa-4x text-primary"></i>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="editUsername" required>
              <label for="editUsername">
                <i class="fas fa-user me-1 text-primary"></i>Nom d'utilisateur
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="eManagerFullname" required>
              <label for="eManagerFullname">
                <i class="fas fa-id-card me-1 text-primary"></i>Nom complet
              </label>
              <small class="form-text text-muted">Entre 5 et 50 caractères</small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="editEmail" name="email">
              <label for="editEmail">
                <i class="fas fa-envelope me-1 text-primary"></i>Email
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="editPhone" name="phone">
              <label for="editPhone">
                <i class="fas fa-phone-alt me-1 text-primary"></i>Téléphone
              </label>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annuler
          </button>
          <button type="button" id="passwordResetBtn" class="btn btn-warning position-relative"
            data-email="" data-bs-toggle="modal" data-bs-target="#passwordResetModal">
            <i class="fas fa-key me-1"></i>Mot de passe
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <i class="fas fa-lock-open"></i>
            </span>
          </button>
          <button type="submit" id="submitProfileMod" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
            <span class="ms-1 spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de réinitialisation du mot de passe -->
<div class="modal fade" id="passwordResetModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="passwordResetLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-warning text-white">
        <h5 class="modal-title" id="passwordResetLabel">
          <i class="fas fa-key me-2 pulse"></i>Réinitialisation du mot de passe
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <div class="password-reset-icon rounded-circle mx-auto bg-light d-flex align-items-center justify-content-center"
            style="width: 80px; height: 80px;">
            <i class="fas fa-envelope-open-text fa-3x text-warning"></i>
          </div>
        </div>

        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="fas fa-info-circle me-2"></i>
          <div>
            Voulez-vous envoyer un lien de réinitialisation à <b id="managerEmailSpaned" class="text-primary"></b> ?
          </div>
        </div>

        <div class="reset-confirmation d-none alert alert-success">
          <i class="fas fa-check-circle me-2"></i>
          Un email a été envoyé avec succès !
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Annuler
          </button>
          <button type="submit" id="sendRPLink" class="btn btn-success">
            <i class="fas fa-paper-plane me-1"></i>Envoyer
            <span class="ms-1 spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CSS pour les animations -->
<style>
  /* Animation de pulsation pour les icônes */
  .pulse {
    animation: pulse 1.5s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }

  /* Dégradés pour les en-têtes */
  .bg-gradient-primary {
    background: linear-gradient(45deg, #4e73df, #224abe);
  }

  .bg-gradient-warning {
    background: linear-gradient(45deg, #f6c23e, #dda20a);
  }

  /* Hover effect sur les boutons */
  .btn {
    transition: all 0.3s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  /* Effet de hover sur les inputs */
  .form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    border-color: #bac8f3;
  }

  /* Avatar upload hover effect */
  .avatar-upload {
    transition: all 0.3s ease;
  }

  .avatar-upload:hover {
    transform: scale(1.05);
  }
</style>