<style>
  .feedback-id {
    width: 5% !important;
  }

  .feedback-email {
    width: 25% !important;
  }

  .feedback-subject {
    width: 30% !important;
  }
</style>
<div class="modal fade" id="feedbacksModal" tabindex="-1" aria-labelledby="feedbacksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="feedbacksModalLabel">Liste des Messages</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container">
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="input-group">
                <input type="text" id="searchFeedbacks" class="form-control" placeholder="Rechercher...">
                <button class="btn btn-outline-secondary" type="button" id="searchFeedbackButton">
                  <i class="fa fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <table class="table table-bordered text-center">
          <thead class="table-dark">
            <tr>
              <th class="feedback-id">ID</th>
              <th class="feedback-email">Email</th>
              <th class="feedback-subject">Sujet</th>
              <th class="feedback-date">
                <span>Date</span>
                <button class="btn" style="color: white;" id="feedbacksSortBtn" data-sort-direction="desc">
                  <i class="fa fa-chevron-down"></i>
                </button>
              </th>
              <th class="feedback-actions">Actions</th>
            </tr>
          </thead>
          <tbody id="feedbacksTableBody">
            <!-- Les messages seront insérés ici via AJAX -->
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-between mt-3">
          <button id="feedbackPrevPage" class="btn btn-outline-primary" disabled>Précédent</button>
          <span id="feedbackPaginationInfo"></span>
          <button id="feedbackNextPage" class="btn btn-outline-primary">Suivant</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Détails du Message -->
<div class="modal fade" id="feedbackDetailsModal" tabindex="-1" aria-labelledby="feedbackDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="feedbackDetailsLabel">Détails du message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <p><strong>Nom :</strong> <span id="feedbackName"></span></p>
            <p><strong>Email :</strong> <span id="feedbackEmail"></span></p>
            <p><strong>Date de réception :</strong> <span id="feedbackDate"></span></p>
          </div>
          <div class="col-md-6">
            <p><strong>Sujet :</strong> <span id="feedbackSubject"></span></p>
          </div>
        </div>
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                Message reçu
              </div>
              <div class="card-body">
                <p id="feedbackMessage"></p>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="replyMessage" class="form-label">Message</label>
              <textarea class="form-control" id="replyMessage" rows="5" placeholder="Votre réponse..."></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSendReply"><i class="fa fa-paper-plane"></i> Envoyer</button>
        <button type="button" class="btn btn-danger" id="btnDeleteFeedback"><i class="fa fa-trash"></i> Supprimer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast de confirmation -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 2100">
  <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Notification</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="notificationMessage">
    </div>
  </div>
</div>

<div id="fecustom-toast" data-confirm="" data-id="" class="custom-toast" style="z-index: 2099 !important; display: none;">
  <p>Voulez-vous vraiment supprimer ce message ?</p>
  <button id="toast-confirm">Supprimer</button>
  <button id="toast-cancel" onclick="$('#fecustom-toast').fadeOut()">Annuler</button>
</div>
