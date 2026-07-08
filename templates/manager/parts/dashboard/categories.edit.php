<!-- Modal pour Ajouter une Nouvelle Catégorie -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editCategoryModalLabel">Modifier la Catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="category-edit-alerts">
        </div>
        <div class="mb-3">
          <label for="editCategoryTitle" class="form-label">Titre</label>
          <input type="text" class="form-control" id="editCategoryTitle" placeholder="Entrez le titre">
        </div>
        <div class="mb-3">
          <label for="editCategoryDescription" class="form-label">Description</label>
          <textarea class="form-control" id="editCategoryDescription" placeholder="Entrez une description"></textarea>
        </div>
        <div class="mb-3">
          <label for="editCategoryTags" class="form-label">Tags</label>
          <textarea class="form-control" id="editCategoryTags" placeholder="Entrez des Tags"></textarea>
        </div>
        <div class="mb-3">
          <label for="editCategoryReduction" class="form-label">Réduction (%)</label>
          <input type="number" class="form-control" id="editCategoryReduction" placeholder="Ex: 10">
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-success" id="editCategoryBtn">Modifier</button>
      </div>
    </div>
  </div>
</div>