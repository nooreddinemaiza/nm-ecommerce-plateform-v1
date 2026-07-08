<?php

use Src\Helpers\FileAndPathManager;

$tables = $data['tables'];
foreach ($tables as $key => $value) {
    $$key = $value;
}
function parts($name, $data)
{
    FileAndPathManager::includeFile('manager-part', "/db/$name", $data);
}
parts('voir.contact.php', []);
parts('voir.connexion.php', []);
parts('voir.commandes.php', []);
parts('modifier.contact.php', []);
parts('ajouter.contact.php', []);
?>
<div class="navbar">
    <div class="app-title"></div>
</div>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="bg-light rounded shadow-sm p-3">
                <h4 class="text-center mb-4">Tables</h4>
                <h4 class="text-center mb-4">
                    <a href="/dashboard"><i class="fa fa-backward" aria-hidden="true"></i> Tableau de bord</a>
                </h4>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary table-btn" onclick="showTable('contacts')">Messages</button>
                    <button class="btn btn-outline-secondary table-btn" onclick="showTable('logins')">Connexions</button>
                    <button class="btn btn-outline-secondary table-btn" onclick="showTable('orders')">Commandes</button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Contacts Table -->
            <div id="contacts" class="table-details mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Table: Contacts</h4>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" id="select-all-contacts" class="form-check-input me-1 select-all-checkbox" onchange="toggleSelectAll('contacts')">
                        <label for="select-all-contacts" class="me-2" style="cursor: pointer;" title="Tout sélectionner">Tout sélectionner</label>
                        <button id="delete-selected-contacts" data-table="contacts" class="btn btn-sm btn-danger delete-selected-btn" title="Supprimer la sélection">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-success add-btn" title="Ajouter un contact" data-table="contacts">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <span id="contacts-selection-info" class="text-muted d-block mb-2">aucune sélection</span>
                <div class="table-responsive table-wrapper">
                    <table class="table table-bordered table-hover table-fixed mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Sélect.</th>
                                <th style="width: 50px;">ID</th>
                                <th style="min-width: 150px;">Nom</th>
                                <th style="min-width: 200px;">Email</th>
                                <th style="min-width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="contacts-tbody">
                            <?php if (count($contacts)) {
                                foreach ($contacts as $contact) { ?>
                                    <tr class="line" data-id="<?= $contact['id'] ?>" data-table="contacts" data-infos='<?= json_encode([
                                                                                                                            "id" => $contact['id'] ?? 'Non spécifié',
                                                                                                                            "name" => htmlentities($contact['name'] ?? 'Non spécifié'),
                                                                                                                            "email" => htmlentities($contact['email'] ?? 'Non spécifié'),
                                                                                                                            "subject" => htmlentities($contact['subject'] ?? 'Non spécifié'),
                                                                                                                            "message" => htmlentities($contact['message'] ?? 'Non spécifié'),
                                                                                                                            "ip_address" => htmlentities($contact['ip_address'] ?? 'Non spécifié'),
                                                                                                                            "user_agent" => htmlentities($contact['user_agent'] ?? 'Non spécifié'),
                                                                                                                        ]) ?>'>
                                        <td><input type="checkbox" class="form-check-input row-checkbox" onchange="updateSelection('contacts')"></td>
                                        <td><?= $contact['id'] ?></td>
                                        <td><?= $contact['name'] ?></td>
                                        <td><?= $contact['email'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-message">Voir</button>
                                            <button class="btn btn-sm btn-warning edit-btn">Modifier</button>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucun contact trouvé.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Logins Table -->
            <div id="logins" class="table-details">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Table: Connexions</h4>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" id="select-all-logins" class="form-check-input me-1 select-all-checkbox" onchange="toggleSelectAll('logins')">
                        <label for="select-all-logins" class="me-2" style="cursor: pointer;" title="Tout sélectionner">Tout sélectionner</label>
                        <button id="delete-selected-logins" data-table="logins" class="btn btn-sm btn-danger delete-selected-btn" title="Supprimer la sélection">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <span id="logins-selection-info" class="text-muted d-block mb-2">aucune sélection</span>
                <div class="table-responsive table-wrapper">
                    <table class="table table-bordered table-hover table-fixed mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="min-width: 150px;">Status</th>
                                <th style="min-width: 200px;">Adresse IP</th>
                                <th style="min-width: 200px;">Tentatives</th>
                                <th style="min-width: 220px;">Actions</th>
                                <th style="width: 50px;">Sélect.</th>
                            </tr>
                        </thead>
                        <tbody id="logins-tbody">
                            <?php if (count($logins)) {
                                foreach ($logins as $login) { ?>
                                    <tr class="line" data-id="<?= $login['id'] ?>" data-table="logins" data-infos='<?= json_encode([
                                                                                                                        "id" => $login['id'] ?? 'Non spécifié',
                                                                                                                        "user_id" => htmlentities($login['user_id'] ?? 'Non spécifié'),
                                                                                                                        "ip_address" => htmlentities($login['ip_address'] ?? 'Non spécifié'),
                                                                                                                        "attempts" => htmlentities($login['attempts'] ?? 'Non spécifié'),
                                                                                                                        "status" => htmlentities($login['status'] ?? 'Non spécifié'),
                                                                                                                        "last_attempt" => htmlentities($login['last_attempt'] ?? 'Non spécifié'),
                                                                                                                        "user_agent" => htmlentities($login['user_agent'] ?? 'Non spécifié'),
                                                                                                                    ]) ?>'>
                                        <td><?= $login['id'] ?? 'Non spécifié' ?></td>
                                        <td class="login-status-td">
                                            <?= trim(htmlentities($login['status'])) == "blocked" ? "Bloqué" : "--" ?>
                                        </td>
                                        <td><?= trim(htmlentities($login['ip_address'])) ?></td>
                                        <td><?= trim(htmlentities($login['attempts'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-login" title="Voir">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm login-bloque" data-block="<?= trim(htmlentities($login['status'])) ?>" title="<?= trim(htmlentities($login['status'])) == "blocked" ? "Débloquer" : "Bloquer" ?>">
                                                <?= trim(htmlentities($login['status'])) == "blocked"
                                                    ? '<i class="fas fa-user-check text-success"></i>'
                                                    : '<i class="fas fa-user-lock text-danger"></i>' ?>
                                            </button>
                                        </td>
                                        <td><input type="checkbox" class="form-check-input row-checkbox" onchange="updateSelection('logins')"></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun record à ce moment.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- orders Table -->
            <div id="orders" class="table-details">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Table: Commandes</h4>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" id="select-all-orders" class="form-check-input me-1 select-all-checkbox" onchange="toggleSelectAll('orders')">
                        <label for="select-all-orders" class="me-2" style="cursor: pointer;" title="Tout sélectionner">Tout sélectionner</label>
                        <button id="delete-selected-orders" data-table="orders" class="btn btn-sm btn-danger delete-selected-btn" title="Supprimer la sélection">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div id="fiterStat2" class="d-flex flex-wrap gap-2">
                        <label><input type="checkbox" value="pending" class="form-check-input me-1 cmdStat2"> En attente</label>
                        <label><input type="checkbox" value="processing" class="form-check-input me-1 cmdStat2"> En traitement</label>
                        <label><input type="checkbox" value="shipped" class="form-check-input me-1 cmdStat2"> Expédié</label>
                        <label><input type="checkbox" value="delivered" class="form-check-input me-1 cmdStat2"> Livré</label>
                        <label><input type="checkbox" value="cancelled" class="form-check-input me-1 cmdStat2"> Annulé</label>
                        <label><input type="checkbox" value="returned" class="form-check-input me-1 cmdStat2"> Retourné</label>
                    </div>
                </div>
                <span id="orders-selection-info" class="text-muted d-block mb-2"></span>
                <div class="table-responsive table-wrapper">
                    <table class="table table-bordered table-hover table-fixed mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="min-width: 150px;">Nom</th>
                                <th style="min-width: 200px;">Date</th>
                                <th style="min-width: 220px;">Status</th>
                                <th style="width: 50px;">Sélect.</th>
                            </tr>
                        </thead>
                        <tbody id="orders-tbody">
                            <?php if (count($orders)) {
                                foreach ($orders as $order) { ?>
                                    <tr class="line" data-id="<?= $order['id'] ?>" data-table="orders" data-infos='<?= json_encode([
                                                                                                                        "id" => $order['id'] ?? 'Non spécifié',
                                                                                                                        "customer_name" => htmlentities($order['customer_name'] ?? 'Non spécifié'),
                                                                                                                        "customer_email" => htmlentities($order['customer_email'] ?? 'Non spécifié'),
                                                                                                                        "order_date" => htmlentities($order['order_date'] ?? 'Non spécifié'),
                                                                                                                        "status" => htmlentities($order['status'] ?? 'Non spécifié'),
                                                                                                                        "total_amount" => htmlentities($order['total_amount'] ?? 'Non spécifié'),
                                                                                                                        "printed" => htmlentities($order['printed'] ?? 'Non spécifié'),
                                                                                                                        "customer_city" => htmlentities($order['customer_city'] ?? 'Non spécifié'),
                                                                                                                        "customer_city_zip" => htmlentities($order['customer_city_zip'] ?? 'Non spécifié'),
                                                                                                                        "customer_address" => htmlentities($order['customer_address'] ?? 'Non spécifié'),
                                                                                                                    ]) ?>'>





                                        <td><?= $order['id'] ?? 'Non spécifié' ?></td>
                                        <td><?= $order['customer_name'] ?? 'Non spécifié' ?></td>
                                        <td><?= $order['order_date'] ?? 'Non spécifié' ?></td>
                                        <td class="order-status-td">
                                            <?= trim(htmlentities($order['status'])) ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-order" title="Voir">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                        <td><input type="checkbox" class="form-check-input row-checkbox" onchange="updateSelection('orders')"></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun record à ce moment.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast de suppression -->

<div id="custom-toast" data-confirm="" data-id="" class="custom-toast" style="z-index: 2099 !important; bottom:90px; display: none;">
    <p>Confirmer la suppression ?</p>
    <button id="toast-confirm">Supprimer</button>
    <button id="toast-cancel" onclick="$('#custom-toast').fadeOut()">Annuler</button>
</div>