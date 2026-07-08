let id = null;
let table = null;
let to = null;

// Sélectionner tous les checkboxes de statut
const statusCheckboxes = document.querySelectorAll('.cmdStat2');

// Fonction pour filtrer le tableau selon les statuts sélectionnés
function filterTableByStatus() {
    const selectedStatuses = Array.from(statusCheckboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);

    const tbody = document.getElementById('orders-tbody');
    const rows = tbody.querySelectorAll('tr.line');

    // Réinitialiser la table
    rows.forEach(row => {
        row.classList.remove('filtered-out');

        // Vérifier si ce statut est filtré
        if (selectedStatuses.length > 0) {
            const statusCell = row.querySelector('.order-status-td');
            if (statusCell) {
                const rowStatus = statusCell.textContent.trim().toLowerCase();

                if (!selectedStatuses.includes(rowStatus)) {
                    row.classList.add('filtered-out');
                }
            }
        }
    });

    // Mettre à jour l'état de sélection
    updateSelection('orders');
}

// Fonction pour afficher une table et masquer les autres
function showTable(tableName) {
    // Masquer toutes les tables
    const tables = document.querySelectorAll('.table-details');
    tables.forEach(table => {
        table.style.display = 'none';
    });

    // Afficher la table sélectionnée
    document.getElementById(tableName).style.display = 'block';

    // Mettre à jour la classe active des boutons
    const buttons = document.querySelectorAll('.table-btn');
    buttons.forEach(button => {
        button.classList.remove('active');
        if (button.textContent.toLowerCase() === tableName.toLowerCase()) {
            button.classList.add('active');
        }
    });
}

function checkSessionStatus() {
    $.ajax({
        url: '/session-expired',
        method: 'POST',
        success: function (data) {
            if (data.expired) {
                afficherMessageSessionExpiree();
            }
        }, error: function () {
            afficherMessageSessionExpiree();
        }
    });
}

function showToaster(message) {
    // Si la fonction n'existe pas encore, la créer
    if (typeof window.toasterTimeout !== 'undefined') {
        clearTimeout(window.toasterTimeout);
    }
    
    // Créer le toaster s'il n'existe pas
    if (!document.getElementById('info-toaster')) {
        const toaster = document.createElement('div');
        toaster.id = 'info-toaster';
        toaster.style.position = 'fixed';
        toaster.style.bottom = '20px';
        toaster.style.right = '20px';
        toaster.style.padding = '10px 20px';
        toaster.style.background = '#333';
        toaster.style.color = 'white';
        toaster.style.borderRadius = '5px';
        toaster.style.boxShadow = '0 0 10px rgba(0,0,0,0.2)';
        toaster.style.zIndex = '9999';
        toaster.style.display = 'none';
        document.body.appendChild(toaster);
    }
    
    const toaster = document.getElementById('info-toaster');
    toaster.textContent = message;
    toaster.style.display = 'block';
    
    window.toasterTimeout = setTimeout(() => {
        toaster.style.display = 'none';
    }, 3000);
}

// Fonction pour récupérer les IDs des lignes sélectionnées
function getSelectedIds(tableName) {
    const tbody = document.getElementById(`${tableName}-tbody`);
    const selectedRows = tbody.querySelectorAll('tr.line.selected');
    return Array.from(selectedRows).map(row => row.dataset.id);
}

window.onload = function () {
    showTable('contacts');
};

// Attend que le DOM soit prêt
$(document).ready(function () {
    setInterval(checkSessionStatus, (3.5) * 60 * 1000);
    
    // Sélectionne tous les boutons "Voir"
    document.querySelectorAll('.view-message').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Récupère la ligne parente
            const tr = btn.closest('tr');
            const infos = JSON.parse(tr.dataset.infos);

            // Remplit les champs du modal
            document.getElementById('modal-id').textContent = infos.id || '';
            document.getElementById('modal-name').textContent = infos.name || '';
            document.getElementById('modal-email').textContent = infos.email || '';
            document.getElementById('modal-subject').textContent = infos.subject || 'Non spécifié';
            document.getElementById('modal-message').textContent = infos.message || 'Non spécifié';
            document.getElementById('modal-ip').textContent = infos.ip_address || 'Inconnue';
            document.getElementById('modal-agent').textContent = infos.user_agent || 'Non défini';

            // Affiche le modal
            const modal = new bootstrap.Modal(document.getElementById('clientMessageModal'));
            modal.show();
        });
    });

    document.querySelectorAll('.view-login').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Récupère la ligne parente
            const tr = btn.closest('tr');
            const infos = JSON.parse(tr.dataset.infos);

            // Remplit les champs du modal
            document.getElementById('lgmodal-id').textContent = infos.id || 'Non spécifié';
            document.getElementById('lgmodal-status').textContent = infos.status || 'Non spécifié';
            document.getElementById('lgmodal-attempts').textContent = infos.attempts || 'Non spécifié';
            document.getElementById('lgmodal-last_attempt').textContent = infos.last_attempt || 'Non spécifié';
            document.getElementById('lgmodal-ip').textContent = infos.ip_address || 'Inconnue';
            document.getElementById('lgmodal-agent').textContent = infos.user_agent || 'Non défini';

            // Affiche le modal
            const modal = new bootstrap.Modal(document.getElementById('loginsModal'));
            modal.show();
        });
    });

    document.querySelectorAll('.view-order').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Récupère la ligne parente
            const tr = btn.closest('tr');
            const infos = JSON.parse(tr.dataset.infos);

            // Remplit les champs du modal avec fallback "Non spécifié"
            document.getElementById('orderModal-id').textContent = infos.id || 'Non spécifié';
            document.getElementById('orderModal-customer_name').textContent = infos.customer_name || 'Non spécifié';
            document.getElementById('orderModal-customer_email').textContent = infos.customer_email || 'Non spécifié';
            document.getElementById('orderModal-customer_phone').textContent = infos.customer_phone || 'Non spécifié';
            document.getElementById('orderModal-order_date').textContent = infos.order_date || 'Non spécifié';
            document.getElementById('orderModal-status').textContent = infos.status || 'Non spécifié';
            document.getElementById('orderModal-printed').textContent = infos.printed || 'Non spécifié';
            document.getElementById('orderModal-customer_city').textContent = infos.customer_city || 'Non spécifié';
            document.getElementById('orderModal-customer_city_zip').textContent = infos.customer_city_zip || 'Non spécifié';
            document.getElementById('orderModal-customer_address').textContent = infos.customer_address || 'Non spécifié';
            document.getElementById('orderModal-total_amount').textContent = infos.total_amount || 'Non spécifié';

            // Affiche la modale
            const modal = new bootstrap.Modal(document.getElementById('ordersModal'));
            modal.show();
        });
    });

    document.querySelectorAll('.edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tr = btn.closest('tr');
            const infos = JSON.parse(tr.dataset.infos);
            id = tr.dataset.id;
            table = tr.dataset.table;
            to = infos;
            
            // Remplir les champs du formulaire
            document.getElementById('edit-name').value = infos.name || '';
            document.getElementById('edit-email').value = infos.email || '';
            document.getElementById('edit-subject').value = infos.subject || '';
            document.getElementById('edit-message').value = infos.message || '';
            document.getElementById('edit-ip').value = infos.ip_address || '';
            document.getElementById('edit-agent').value = infos.user_agent || '';

            // Affiche le modal
            const editModal = new bootstrap.Modal(document.getElementById('editClientModal'));
            editModal.show();
        });
    });

    $('#subMContact').on('click', function () {
        let data = {};
        const nameEdt = document.getElementById('edit-name').value;
        const emailEdt = document.getElementById('edit-email').value;
        const subjectEdt = document.getElementById('edit-subject').value;
        const messageEdt = document.getElementById('edit-message').value;
        const ipEdt = document.getElementById('edit-ip').value;
        const agentEdt = document.getElementById('edit-agent').value;
        
        if (to.id != id) {
            showToaster('Une erreur est survenue, Veuillez actualiser la page est ressayer!');
            return;
        }
        
        if (to.name != nameEdt) {
            data.name = nameEdt;
        }
        if (to.email != emailEdt) {
            data.email = emailEdt;
        }
        if (to.subject != subjectEdt) {
            data.subject = subjectEdt;
        }
        if (to.message != messageEdt) {
            data.message = messageEdt;
        }
        if (to.ip_address != ipEdt) {
            data.ip_address = ipEdt;
        }
        if (to.user_agent != agentEdt) {
            data.user_agent = agentEdt;
        }
        
        if (Object.keys(data).length > 0) {
            data.id = id;
            data.table = table;
            $.ajax({
                type: "POST",
                url: "/database/update",
                data: {
                    data: data
                },
                success: function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            showToaster("Mise à jour réussie !");
                            $("#editClientModal").modal('hide');
                        } else {
                            showToaster(response.message || "Une erreur est survenue.");
                        }
                    } catch (e) {
                        showToaster("Une erreur est survenue lors du traitement.");
                    }
                },
                error: function () {
                    showToaster("Une erreur réseau est survenue.");
                }
            });
        } else {
            showToaster("Aucune modification détectée.");
        }
    });

    document.querySelectorAll('.login-bloque').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tr = btn.closest('tr');
            const td = tr.querySelector('.login-status-td');
            const infos = JSON.parse(tr.dataset.infos);
            let block = btn.dataset.block == "blocked" ? "" : "blocked";
            let im;

            // Désactive le bouton pour éviter les clics multiples
            btn.disabled = true;

            if (block === "") {
                // Déblocage
                btn.setAttribute("title", 'Bloquer');
                td.innerHTML = "--";
                im = '<i class="fas fa-user-lock text-danger"></i>';
                btn.dataset.block = "";
            } else {
                // Blocage
                btn.dataset.block = "blocked";
                td.innerHTML = "Bloqué";
                btn.setAttribute("title", 'Débloquer');
                im = '<i class="fas fa-user-check text-success"></i>';
            }

            $.ajax({
                type: "POST",
                url: "/database/update",
                data: {
                    data: {
                        id: tr.dataset.id,
                        table: tr.dataset.table,
                        status: block,
                    }
                },
                success: function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            btn.innerHTML = im;
                            showToaster("Mise à jour réussie !");
                        } else {
                            showToaster(response.message || "Une erreur est survenue.");
                        }
                    } catch (e) {
                        showToaster("Une erreur est survenue lors du traitement.");
                    }
                    btn.disabled = false; // Réactive le bouton dans tous les cas
                },
                error: function () {
                    showToaster("Une erreur réseau est survenue.");
                    btn.disabled = false; // Même en cas d'erreur
                }
            });
        });
    });

    $(".add-btn").on('click', function () {
        table = ($(this).data("table"));
        $("#addClientModal").modal('show');
    });

    $("#subAContact").on('click', function () {
        const email = $("#addemail").val() ?? "";
        const name = $("#addname").val() ?? "";
        const subject = $("#addsubject").val() ?? "";
        const message = $("#addmessage").val() ?? "";
        const ip_address = $("#addip").val() ?? "";
        const user_agent = $("#adduser_agent").val() ?? "";
        let data = {};
        
        if (email.length) {
            data.email = email;
        }
        if (name.length) {
            data.name = name;
        }
        if (subject.length) {
            data.subject = subject;
        }
        if (message.length) {
            data.message = message;
        }
        if (ip_address.length) {
            data.ip_address = ip_address;
        }
        if (user_agent.length) {
            data.user_agent = user_agent;
        }
        
        if (Object.keys(data).length > 0) {
            data.table = table;
            $.ajax({
                type: "POST",
                url: "/database/add",
                data: {
                    data: data
                },
                success: function (response) {
                    try {
                        response = JSON.parse(response);

                        if (response.success) {
                            showToaster("Ajout réussi, actualisez la page pour voir le changement!");
                            
                            // Réinitialiser les champs du formulaire
                            $("#addemail").val('');
                            $("#addname").val('');
                            $("#addsubject").val('');
                            $("#addmessage").val('');
                            $("#addip").val('');
                            $("#adduser_agent").val('');

                            $("#addClientModal").modal('hide');
                        } else {
                            showToaster(response.message || "Une erreur est survenue.");
                        }
                    } catch (e) {
                        showToaster("Une erreur est survenue lors du traitement.");
                    }
                },
                error: function () {
                    showToaster("Une erreur réseau est survenue.");
                }
            });
        } else {
            showToaster("Veuillez remplir au moins un champ.");
        }
    });

    $(".delete-selected-btn").on('click', function () {
        const tableName = $(this).data('table');
        id = "";
        // Correction : Utiliser la fonction getSelectedIds pour obtenir les IDs
        const selectedIds = getSelectedIds(tableName);
        
        if (selectedIds.length === 0) {
            showToaster("Aucun élément sélectionné.");
            return;
        }
        
        id = selectedIds.join(',');
        table = tableName;
        to = "delete";
        $("#custom-toast").fadeIn();
    });

    $("#toast-confirm").on("click", function () {
        switch (to) {
            case "delete":
                if (id) {
                    $.ajax({
                        type: "POST",
                        url: "/database/delete",
                        data: {
                            id: id,
                            table: table
                        },
                        success: function (response) {
                            try {
                                response = JSON.parse(response);
                                if (response.success) {
                                    // On masque le toast
                                    $("#custom-toast").fadeOut();

                                    // Suppression des lignes correspondant aux ids envoyés
                                    const ids = id.split(',');
                                    ids.forEach(idToRemove => {
                                        const rowToRemove = document.querySelector(`#${table}-tbody tr[data-id="${idToRemove}"]`);
                                        if (rowToRemove) {
                                            rowToRemove.remove();
                                        }
                                    });
                                    
                                    // Mise à jour de l'interface après suppression
                                    updateSelection(table);
                                    showToaster(response.message || "Suppression réussie");
                                } else {
                                    showToaster(response.message || "Une erreur est survenue.");
                                }
                            } catch (e) {
                                showToaster("Une erreur est survenue lors du traitement de la réponse.");
                            }
                        },
                        error: function () {
                            showToaster("Une erreur réseau est survenue.");
                        }
                    });
                }
                break;
            default:
                break;
        }
    });

    // Ajouter les événements aux cases à cocher de statut
    statusCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', filterTableByStatus);
    });

    // Ajouter du CSS pour cacher les lignes filtrées
    const style = document.createElement('style');
    style.textContent = `
        .filtered-out { display: none !important; }
        tr.selected { background-color: rgba(0, 123, 255, 0.1); }
        .row-checkbox { cursor: pointer; }
    `;
    document.head.appendChild(style);

    // AMÉLIORATIONS des fonctions de sélection

    // Sélection de toutes les lignes visibles
    window.toggleSelectAll = function (tableName) {
        const selectAllCheckbox = document.getElementById(`select-all-${tableName}`);
        const isChecked = selectAllCheckbox.checked;
        const tbody = document.getElementById(`${tableName}-tbody`);
        const visibleRows = tbody.querySelectorAll('tr.line:not(.filtered-out)');

        visibleRows.forEach(row => {
            const checkbox = row.querySelector('.row-checkbox');
            if (checkbox) {
                checkbox.checked = isChecked;
                row.classList.toggle('selected', isChecked);
            }
        });

        updateSelection(tableName);
    };

    // Mise à jour de l'affichage de la sélection
    window.updateSelection = function (tableName) {
        const tbody = document.getElementById(`${tableName}-tbody`);
        if (!tbody) {
            console.warn(`tbody introuvable pour : ${tableName}-tbody`);
            return;
        }

        // Compter seulement les lignes visibles
        const visibleCheckboxes = tbody.querySelectorAll('tr.line:not(.filtered-out) .row-checkbox');
        const totalVisibleCheckboxes = visibleCheckboxes.length;
        let checkedCount = 0;

        // Mettre à jour l'état de toutes les lignes
        const allCheckboxes = tbody.querySelectorAll('.row-checkbox');
        allCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (checkbox.checked) {
                row.classList.add('selected');
                // Ne compter que les lignes visibles
                if (!row.classList.contains('filtered-out')) {
                    checkedCount++;
                }
            } else {
                row.classList.remove('selected');
            }
        });

        // Mettre à jour l'état de la case "Tout sélectionner"
        const selectAllCheckbox = document.getElementById(`select-all-${tableName}`);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = (checkedCount === totalVisibleCheckboxes) && (totalVisibleCheckboxes > 0);
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalVisibleCheckboxes;
        }

        // Afficher/masquer le bouton de suppression et mettre à jour l'info
        const deleteSelectedBtn = document.getElementById(`delete-selected-${tableName}`);
        const selectionInfoElement = document.getElementById(`${tableName}-selection-info`);

        // Sélectionner toutes les lignes marquées comme sélectionnées
        const totalSelectedRows = tbody.querySelectorAll('tr.line.selected').length;

        if (deleteSelectedBtn) {
            deleteSelectedBtn.style.display = totalSelectedRows > 0 ? 'inline-block' : 'none';
        }

        if (selectionInfoElement) {
            selectionInfoElement.style.display = totalSelectedRows > 0 ? 'inline' : 'none';
            selectionInfoElement.textContent = totalSelectedRows > 0
                ? `${totalSelectedRows} selection${totalSelectedRows > 1 ? 's' : ''}`
                : 'aucune selection';
        }
    };

    // Event listeners pour les checkboxes des lignes
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            row.classList.toggle('selected', this.checked);
            
            // Mettre à jour l'affichage de la sélection pour la table correspondante
            const tableName = row.dataset.table;
            if (tableName) {
                updateSelection(tableName);
            }
        });
    });
    // Initialiser l'état de sélection pour chaque table
    ['contacts', 'logins', 'orders'].forEach(tableName => {
        updateSelection(tableName);
    });
});