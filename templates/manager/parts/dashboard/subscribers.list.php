<!-- Modal -->
<div class="modal fade" id="noSubsModal" tabindex="-1" aria-labelledby="noSubsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="noSubsModalLabel">Liste des Abonnés</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" id="select-all" style='cursor:pointer' title="Sélectionner tout" />
                </th>
                <th>Email</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
          </table>
        </div>
        <div style="max-height: 50vh; overflow-y: auto;">
          <!-- Table des non-abonnés -->
          <table class="table table-bordered">
            <tbody id="noSubsTableBody">
              <!-- Les données seront insérées ici via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" id="delete-selected">Supprimer la sélection</button>
        <button class="btn btn-warning" id="notify-selected">Notifier la sélection</button>
      </div>
    </div>
  </div>
</div>

