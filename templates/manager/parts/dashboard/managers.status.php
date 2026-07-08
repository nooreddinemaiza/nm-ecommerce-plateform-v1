<!-- Modal pour modifier le rôle et le statut -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="editUserModalLabel">Réinitialiser le mot de passe de <b id="managerNameTitle">Test</b></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editUserForm">
          <input type="hidden" id="editUserId" name="id">
          <div class="mb-3">
            <label for="editPassword" class="form-label">Nouveau mot de passe</label>
            <small class="text-muted" style="color: red !important;">Laissez vide pour ne pas changer le mot de passe</small>
            <input type="password" class="form-control" id="editPassword" name="password">
          </div>
          <div class="mb-3">
            <label for="editRepeatPassword" class="form-label">Répéter le mot de passe</label>
            <input type="password" class="form-control" id="editRepeatPassword" name="repeat_password">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-warning" id="saveChangesBtn">Enregistrer</button>
      </div>
    </div>
  </div>
</div>