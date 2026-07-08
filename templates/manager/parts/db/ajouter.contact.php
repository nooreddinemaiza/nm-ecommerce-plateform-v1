<!-- Modal de modification -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="overflow-y: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="addClientModalLabel">Ajouter une ligne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <!-- Champ caché pour l'ID -->
        <div class="mb-3">
          <label for="addname" class="form-label">Nom</label>
          <input type="text" class="form-control" id="addname" name="name" required>
        </div>
        <div class="mb-3">
          <label for="addemail" class="form-label">Email</label>
          <input type="email" class="form-control" id="addemail" name="email" required>
        </div>
        <div class="mb-3">
          <label for="addsubject" class="form-label">Sujet</label>
          <input type="text" class="form-control" id="addsubject" name="subject">
        </div>
        <div class="mb-3">
          <label for="addmessage" class="form-label">Message</label>
          <textarea class="form-control" id="addmessage" name="message" rows="4"></textarea>
        </div>
        <div class="mb-3">
          <label for="addip" class="form-label">Adresse IP</label>
          <input type="text" class="form-control" id="addip" name="ip_address" readonly>
        </div>
        <div class="mb-3">
          <label for="add-agent" class="form-label">User Agent</label>
          <textarea class="form-control" id="add-agent" name="user_agent" readonly rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="subAContact">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </div>
  </div>
</div>