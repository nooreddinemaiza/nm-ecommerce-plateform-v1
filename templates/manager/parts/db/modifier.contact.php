<!-- Modal de modification -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="overflow-y: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="editClientModalLabel">Modifier le Message Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <!-- Champ caché pour l'ID -->
        <div class="mb-3">
          <label for="edit-name" class="form-label">Nom</label>
          <input type="text" class="form-control" id="edit-name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="edit-email" class="form-label">Email</label>
          <input type="email" class="form-control" id="edit-email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="edit-subject" class="form-label">Sujet</label>
          <input type="text" class="form-control" id="edit-subject" name="subject">
        </div>
        <div class="mb-3">
          <label for="edit-message" class="form-label">Message</label>
          <textarea class="form-control" id="edit-message" name="message" rows="4"></textarea>
        </div>
        <div class="mb-3">
          <label for="edit-ip" class="form-label">Adresse IP</label>
          <input type="text" class="form-control" id="edit-ip" name="ip_address" readonly>
        </div>
        <div class="mb-3">
          <label for="edit-agent" class="form-label">User Agent</label>
          <textarea class="form-control" id="edit-agent" name="user_agent" readonly rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="subMContact">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>