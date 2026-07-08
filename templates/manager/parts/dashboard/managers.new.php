<!-- Modal pour ajouter un manager - Version créative -->
<div class="modal fade" id="addManagerModal" tabindex="-1" aria-labelledby="addManagerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <!-- Header avec image de fond et design moderne -->
      <div class="modal-header border-0 text-white position-relative p-0">
        <div class="modal-header-bg w-100 py-4 px-4" style="background: linear-gradient(135deg, #6366F1 0%, #2563EB 100%);">
          <div class="position-relative z-2">
            <h4 class="modal-title fs-4 fw-bold mb-1" id="addManagerModalLabel">
              <i class="fa-solid fa-user-plus me-2 fa-bounce"></i>Ajouter un Manager
            </h4>
          </div>
          <div class="position-absolute end-0 top-0 mt-3 me-3">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Éléments décoratifs -->
          <div class="position-absolute end-0 bottom-0 opacity-10">
            <i class="fa-solid fa-users-gear fa-4x"></i>
          </div>
        </div>
      </div>

      <div class="modal-body p-4">
        <!-- Stepper - Indicateur de progression -->
        <div class="mb-4">
          <div class="position-relative">
            <div class="progress" style="height: 4px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;"
                id="addManagerProgress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="position-absolute start-0 top-0 translate-middle p-1 bg-primary rounded-circle">
              <span class="visually-hidden">Étape 1</span>
              <i class="fa-solid fa-check text-white small"></i>
            </div>
            <div class="position-absolute start-50 top-0 translate-middle p-1 bg-secondary rounded-circle" id="step2Indicator">
              <span class="visually-hidden">Étape 2</span>
            </div>
            <div class="position-absolute end-0 top-0 translate-middle p-1 bg-secondary rounded-circle" id="step3Indicator">
              <span class="visually-hidden">Étape 3</span>
            </div>
          </div>
        </div>

        <!-- Zone d'alerte pour les messages avec animation -->
        <div id="add-user-alert" class="mb-3 animate__animated animate__fadeInDown"></div>

        <!-- Formulaire pour ajouter un manager -->
        <form method="post" id="addManagerForm">
          <!-- Section principale -->
          <div class="row g-3">
            <!-- Colonne de gauche -->
            <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-center">
              <div class="avatar-container mb-3">
                <div class="avatar-circle bg-light d-flex align-items-center justify-content-center"
                  style="width: 120px; height: 120px; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                  <i class="fa-solid fa-user-tie fa-4x text-primary opacity-75"></i>
                </div>
              </div>
              <h6 class="fw-bold mb-1">Nouveau Manager</h6>
            </div>

            <!-- Colonne de droite - Formulaire principal -->
            <div class="col-md-8">
              <div class="row g-3">
                <!-- Nom d'utilisateur -->
                <div class="col-md-6">
                  <div class="form-floating mb-0">
                    <input type="text" class="form-control border-0 bg-light" id="managerName" required>
                    <label for="managerName"><i class="fa-solid fa-user me-1 text-primary"></i>Nom d'utilisateur</label>
                  </div>
                  <div class="d-flex justify-content-end mt-1">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="generateUsername" type="button">
                      <i class="fa-solid fa-arrows-rotate me-1"></i>Générer
                    </button>
                  </div>
                </div>

                <!-- Nom complet -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control border-0 bg-light" id="managerFullname" required>
                    <label for="managerFullname"><i class="fa-solid fa-id-card me-1 text-primary"></i>Nom complet</label>
                    <small class="form-text text-muted ms-1 mt-1 d-block">Min 5, max 50 caractères</small>
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control border-0 bg-light" id="managerEmail" required>
                    <label for="managerEmail"><i class="fa-solid fa-envelope me-1 text-primary"></i>Adresse Email</label>
                  </div>
                </div>

                <!-- Téléphone -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control border-0 bg-light" id="managerPhone">
                    <label for="managerPhone"><i class="fa-solid fa-phone me-1 text-primary"></i>Téléphone</label>
                  </div>
                </div>

                <!-- Mot de passe -->
                <div class="col-md-6">
                  <div class="form-floating password-container">
                    <input type="password" class="form-control border-0 bg-light" id="password" required>
                    <label for="password"><i class="fa-solid fa-lock me-1 text-primary"></i>Mot de passe</label>
                    <button class="btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y pe-3 toggle-password"
                      type="button" data-target="password">
                      <i class="fa-solid fa-eye text-secondary"></i>
                    </button>
                  </div>
                  <!-- Indicateur de force du mot de passe -->
                  <div class="password-strength mt-1">
                    <div class="d-flex gap-1">
                      <div class="bar flex-grow-1 rounded" style="height: 4px; background-color: #e9ecef;"></div>
                      <div class="bar flex-grow-1 rounded" style="height: 4px; background-color: #e9ecef;"></div>
                      <div class="bar flex-grow-1 rounded" style="height: 4px; background-color: #e9ecef;"></div>
                      <div class="bar flex-grow-1 rounded" style="height: 4px; background-color: #e9ecef;"></div>
                    </div>
                  </div>
                </div>

                <!-- Répéter le mot de passe -->
                <div class="col-md-6">
                  <div class="form-floating password-container">
                    <input type="password" class="form-control border-0 bg-light" id="repeatPassword" required>
                    <label for="repeatPassword"><i class="fa-solid fa-shield-halved me-1 text-primary"></i>Répéter le mot de passe</label>
                    <button class="btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y pe-3 toggle-password"
                      type="button" data-target="repeatPassword">
                      <i class="fa-solid fa-eye text-secondary"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section de suggestions de mot de passe -->
          <div class="password-suggestions mt-3 mb-3 p-2 rounded bg-light small d-none">
            <div class="d-flex align-items-center mb-1">
              <i class="fa-solid fa-lightbulb text-warning me-2"></i>
              <span class="fw-medium">Suggestions pour un mot de passe fort :</span>
            </div>
            <div class="row g-2 mt-1">
              <div class="col-auto">
                <span class="badge bg-light text-dark border">Utiliser au moins 8 caractères</span>
              </div>
              <div class="col-auto">
                <span class="badge bg-light text-dark border">Inclure des majuscules</span>
              </div>
              <div class="col-auto">
                <span class="badge bg-light text-dark border">Inclure des chiffres</span>
              </div>
              <div class="col-auto">
                <span class="badge bg-light text-dark border">Inclure des symboles</span>
              </div>
            </div>
          </div>

          <!-- Zone d'actions du formulaire -->
          <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="button" class="btn btn-link text-decoration-none">
              <i class="fa-solid fa-arrow-rotate-left me-1"></i>Réinitialiser
            </button>
            <div>
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fa-solid fa-xmark me-1"></i>Annuler
              </button>
              <button type="submit" id="addManagerButon" class="btn btn-primary ms-2 px-4">
                <i class="fa-solid fa-user-check me-1"></i>Ajouter
                <div class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                  <span class="visually-hidden">Chargement...</span>
                </div>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Style supplémentaire pour les animations et effets -->
<style>
  /* Animations et transitions */
  #addManagerModal .form-control {
    transition: box-shadow 0.3s ease, transform 0.2s ease;
  }

  #addManagerModal .form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    transform: translateY(-1px);
  }

  #addManagerModal .avatar-container {
    position: relative;
    transition: all 0.3s ease;
  }

  #addManagerModal .avatar-container:hover {
    transform: scale(1.05);
  }

  #addManagerModal .badge {
    transition: all 0.3s ease;
  }

  #addManagerModal .badge:hover {
    background-color: #e9ecef !important;
    cursor: pointer;
  }

  /* Effet de hover sur les boutons */
  #addManagerModal .btn {
    transition: all 0.3s ease;
  }

  #addManagerModal .btn:hover:not(.disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  /* Animation pour le bouton Générer */
  #generateUsername:active i {
    animation: spin 0.5s linear;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  /* Animation pour l'icône en haut */
  @keyframes floating {
    0% {
      transform: translateY(0px);
    }

    50% {
      transform: translateY(-8px);
    }

    100% {
      transform: translateY(0px);
    }
  }

  #addManagerModal .fa-bounce {
    animation: floating 3s ease infinite;
    transform-origin: center;
  }

  /* Effet de gradient animé pour le header */
  .modal-header-bg {
    background-size: 200% 200% !important;
    animation: gradientBG 15s ease infinite;
  }

  @keyframes gradientBG {
    0% {
      background-position: 0% 50%;
    }

    50% {
      background-position: 100% 50%;
    }

    100% {
      background-position: 0% 50%;
    }
  }
</style>

<!-- Script pour les comportements interactifs -->
<script>
  $(document).ready(function() {
    // Afficher les suggestions de mot de passe lors du focus
    $("#password").focus(function() {
      $(".password-suggestions").removeClass("d-none").addClass("animate__animated animate__fadeIn");
    });

    // Simulation de l'indicateur de force du mot de passe
    $("#password").on("input", function() {
      const password = $(this).val();
      const strength = calculatePasswordStrength(password);
      updatePasswordStrengthIndicator(strength);

      // Mettre à jour la barre de progression
      $("#addManagerProgress").css("width", "33%");
      $("#step2Indicator").removeClass("bg-secondary").addClass("bg-primary");

      // Si le formulaire est bien rempli
      if ($("#managerName").val() && $("#managerFullname").val() && $("#managerEmail").val() &&
        $("#password").val() && $("#repeatPassword").val()) {
        $("#addManagerProgress").css("width", "100%");
        $("#step3Indicator").removeClass("bg-secondary").addClass("bg-primary");
      }
    });

    // Fonction pour calculer la force du mot de passe (simulation)
    function calculatePasswordStrength(password) {
      if (!password) return 0;
      let strength = 0;

      // Longueur
      if (password.length >= 8) strength += 1;

      // Majuscules
      if (/[A-Z]/.test(password)) strength += 1;

      // Chiffres
      if (/[0-9]/.test(password)) strength += 1;

      // Caractères spéciaux
      if (/[^A-Za-z0-9]/.test(password)) strength += 1;

      return strength;
    }

    // Mettre à jour l'indicateur de force du mot de passe
    function updatePasswordStrengthIndicator(strength) {
      const bars = $(".password-strength .bar");
      const colors = ["#dc3545", "#ffc107", "#6c757d", "#198754"];

      bars.css("background-color", "#e9ecef");

      for (let i = 0; i < strength; i++) {
        $(bars[i]).css("background-color", colors[i]);
      }
    }

    // Simulation de la soumission du formulaire
    $("#addManagerForm").submit(function(e) {
      e.preventDefault();

      // Affiche le spinner
      $("#addManagerButon .spinner-border").removeClass("d-none");
      $("#addManagerButon").attr("disabled", true);

      // Simulation du traitement
      setTimeout(() => {
        // Affiche un message de réussite
        $("#add-user-alert").html(`
          <div class="alert alert-success animate__animated animate__fadeIn d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <div>Manager créé avec succès!</div>
          </div>
        `);

        // Cache le spinner
        $("#addManagerButon .spinner-border").addClass("d-none");
        $("#addManagerButon").removeAttr("disabled");

        // Réinitialise le formulaire après un délai
        setTimeout(() => {
          // $("#addManagerForm").trigger("reset");
          // $("#addManagerModal").modal("hide");
        }, 2000);
      }, 1500);
    });

    // Réinitialiser la barre de progression quand le modal s'ouvre
    $("#addManagerModal").on("show.bs.modal", function() {
      $("#addManagerProgress").css("width", "0%");
      $("#step2Indicator, #step3Indicator").removeClass("bg-primary").addClass("bg-secondary");
    });
  });
</script>