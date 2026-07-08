<!-- Manage Categories Modal -->
<div class="modal fade" id="mangeCategoriesModal" tabindex="-1" aria-labelledby="mangeCategoriesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mangeCategoriesModalLabel">Gérer les Catégories</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="category-alerts">
        </div>
        <button class="btn btn-primary mb-3" id="newCategoryBtn">Nouvelle Catégorie</button>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Description</th>
              <th>Tags</th>
              <th>Réduction</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="categoriesTableBody">
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>