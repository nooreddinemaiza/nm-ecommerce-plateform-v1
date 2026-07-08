<div class="modal fade" id="manageManagersModal" tabindex="-1" aria-labelledby="manageManagersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="manageManagersModalLabel">Gérer les Managers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Zone d'alerte pour les messages -->
        <div id="users-alert" class="mb-3"></div>

        <!-- Tableau des managers -->
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="usersTable">
            <thead class="table-dark">
              <tr>
                <th>Nom d'utilisateur</th>
                <th>Status</th>
                <th>Rôle</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Boucle pour afficher les managers -->
              <?php foreach ($data['users'] as $tab):
                $userData = json_encode([
                  'id' => $tab['id'],
                  'username' => $tab['username'],
                  'fullname' => $tab['fullname'],
                  'email' => $tab['email'],
                  'role' => $tab['role'],
                  'status' => $tab['status'],
                  'created_at' => $tab['created_at']
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
              ?>
                <tr data-user-id="<?= $tab['id'] ?>">
                  <?php if ($_SESSION['user_id'] == $tab['id']):
                  endif ?>
                  <td <?php if ($_SESSION['user_id'] == $tab['id']): ?>class="text-success" <?php endif; ?>><b><?= $tab['fullname']  ?? "Anonyme" ?></b> (<?= $tab['username'] ?>)</td>
                  <td>
                    <?php
                    if ($_SESSION['user_id'] != $tab['id']): ?>
                      <div class="role-container">
                        <select class="form-select manStatus-select" data-id="<?= $tab['id'] ?>" data-type="status">
                          <option value="active" <?= $tab['status'] == "active" ? 'selected' :  '' ?>>Actif</option>
                          <option value="inactive" <?= $tab['status'] == "inactive" ? 'selected' : '' ?>>Inactif</option>
                        </select>
                      </div>
                    <?php
                    endif;
                    ?>
                  </td>
                  <td>
                    <?php if ($_SESSION['user_id'] != $tab['id']): ?>
                      <div class="role-container">
                        <select class="form-select manType" data-id="<?= $tab['id'] ?>" data-type="role">
                          <option value="super_manager" <?= $tab['role'] == "super_manager" ? 'selected' : '' ?>>Super Manger</option>
                          <option value="manager" <?= $tab['role'] == "manager" ? 'selected' : '' ?>>Manager</option>
                        </select>
                      </div>
                    <?php endif ?>
                  </td>

                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <!-- Bouton Afficher -->
                      <button class="btn btn-outline-info btn-sm view-btn" data-user='<?= $userData ?>'>
                        <i class="fa-solid fa-eye"></i>
                      </button>
                      <?php if ($_SESSION['user_id'] !== $tab['id']): ?>
                        <!-- Bouton Modifier -->
                        <button class="btn btn-outline-warning btn-sm manager-edit-btn" data-user-id="<?= $tab['id'] ?>">
                          <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <!-- Bouton Supprimer -->
                        <button class="btn btn-outline-danger btn-sm delete-manager" data-user-id="<?= $tab['id'] ?>">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>