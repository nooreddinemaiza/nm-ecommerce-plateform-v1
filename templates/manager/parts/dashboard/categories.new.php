<!-- Modal pour Ajouter une Nouvelle Catégorie -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newCategoryModalLabel">Nouvelle Catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="category-add-alerts">
        </div>
        <div class="mb-3">
          <label for="categoryTitle" class="form-label">Titre</label>
          <input type="text" class="form-control" id="categoryTitle" placeholder="Entrez le titre">
        </div>
        <div class="mb-3">
          <label for="categoryDescription" class="form-label">Description</label>
          <textarea class="form-control" id="categoryDescription" placeholder="Entrez une description"></textarea>
        </div>
        <div class="mb-3">
          <label for="categoryTags" class="form-label">Tags</label>
          <textarea class="form-control" id="categoryTags" placeholder="Entrez des Tags"></textarea>
        </div>
        <div class="mb-3">
          <label for="categoryReduction" class="form-label">Réduction (%)</label>
          <input type="number" class="form-control" id="categoryReduction" placeholder="Ex: 10">
        </div>
        <!-- Nouveau champ pour l'image -->
        <div class="mb-3">
          <label for="categoryImage" class="form-label">Image</label>
          <input type="file" class="form-control" id="categoryImage" accept="image/*">
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-success" id="addCategoryBtn">Ajouter</button>
      </div>
    </div>
  </div>
</div>
