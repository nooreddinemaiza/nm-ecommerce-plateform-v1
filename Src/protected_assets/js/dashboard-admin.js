const stockProduts = {
    tableBody: $('#stockTableBody'),
    pagination: $('#stockPagination'),
    paginationInfo: $('#paginationInfo'),
    searchInput: $('#stockSearchInput'),
    statusFilter: $('#stockStatusFilter'),
    rowsPerPageSelect: $('#rowsPerPage'),
    printBtn: $('#printStockBtn'),
    modal: $('#observeStockModal')
};
$(document).ready(function () {
    API.FETCH_STOCK = '/products/stock/observe';
    $("#generateUsername").click(function () {
        const prefixes = ["manager", "user", "info_manager"];
        const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const randomNum = Math.floor(Math.random() * 1000);
        const username = prefix + randomNum;

        // Animation de l'icône
        $(this).find("i").addClass("fa-spin");

        // Simule la génération avec un court délai
        setTimeout(() => {
            $("#managerName").val(username);
            $(this).find("i").removeClass("fa-spin");

            // Mettre à jour la barre de progression
            $("#addManagerProgress").css("width", "33%");
            $("#step2Indicator").removeClass("bg-secondary").addClass("bg-primary");
        }, 300);
    });

    $(".setMeta").on("click", function (e) {
        e.preventDefault();
        var page = $(this).data("page");
        var metaDesc = $("#" + page + "MetaDesc").val() ?? "";
        var metaKeys = $("#" + page + "MetaKeys").val() ?? "";
        var metaAuth = $("#" + page + "MetaAuth").val() ?? "";
        $.ajax({
            url: "meta-modify", type: "POST", data: { page: page, description: metaDesc, keywords: metaKeys, author: metaAuth }, success: function (response) {
                response = JSON.parse(response);
                showToaster(response.message);

            }, error: function (xhr, status, error) {
                showToaster("Une erreur s'est produite : " + error);

            }
        });
    });
    $('#productSearch').on('input', function () {
        var searchValue = $(this).val().toLowerCase();
        var items = $('#productList li');
        $('#productList').show();
        items.each(function () {
            var itemText = $(this).text().toLowerCase();
            if (itemText.indexOf(searchValue) === -1) {
                $(this).hide();

            } else {
                $(this).show();

            }
        });
    });
    $('#productSearch').on('focus', function () {
        $('#productList').show();

    });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.dropdown').length) {
            $('#productList').hide();

        }
    });
    $('#productList').on('click', 'li', function () {
        selectedProduct = JSON.parse($(this).data('info'));
        $('#productInfo').html(`<p><strong>Titre:</strong> ${selectedProduct.title}</p><p><strong>Prix:</strong> ${selectedProduct.price}</p>`);
        $('#productList').hide();
    });
    $('#modifyBanner').on('click', function () {
        var bannerTitleSmall = $('#bannerTitleSmall').val().trim();
        var bannerTitleBig = $('#bannerTitleBig').val().trim();
        var bannerDescription = $('#bannerDescription').val().trim();
        var productId = selectedProduct ? selectedProduct.id : null;
        if (!bannerTitleSmall || !bannerTitleBig || !bannerDescription || !productId) {
            showToaster('Veuillez remplir tous les champs et sélectionner un produit.');
            return;

        } var formData = { bannerTitleSmall: bannerTitleSmall, bannerTitleBig: bannerTitleBig, bannerDescription: bannerDescription, productId: productId };
        $.ajax({
            url: '/banner-modify', type: 'POST', data: formData, success: function (response) {
                response = JSON.parse(response);
                showToaster(response.message);

            }, error: function () {
                showToaster('Erreur de communication avec le serveur.');

            }
        });
    });

    function loadItems(tabSelector, gridSelector, url, itemType) {
        $(tabSelector).on('click', function () {
            const displayDiv = $(gridSelector);
            displayDiv.html('');
            $.ajax({
                type: "post",
                url: url,
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.success) {
                        const items = data.data;
                        items.forEach(item => {
                            let selectedClass = item.is_trend == 1 ? 'selected' : '';
                            const card = $(`
                            <div class="card d-inline-block me-2 mb-3 category-card ${selectedClass}" style="min-width: 150px; cursor:pointer" data-id="${item.id}">
                                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                                    <div class="btn-group btn-group-sm">
                                        <span class="card-title mb-0">${item.title} </span>
                                    </div>
                                </div>
                            </div>
                        `);
                            displayDiv.append(card);
                        });
                    }
                },
                error: function (xhr, status, error) {
                    showToaster(`Erreur lors du chargement des ${itemType}: ` + error);
                    console.error("Erreur AJAX:", status, error);
                }
            });
        });
    }

    function handleItemClick(gridSelector, url) {
        $(document).on('click', gridSelector + ' .category-card', function () {
            const item = $(this);
            const itemId = item.data('id');
            const isSelected = item.hasClass('selected');


            const data = isSelected ? { remove: itemId, add: '' } : { add: itemId, remove: '' };

            item.toggleClass('selected');

            $.ajax({
                url: url,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function (response) {
                    const parsedResponse = JSON.parse(response);
                    showToaster(parsedResponse.message);
                },
                error: function (xhr, status, error) {
                    showToaster('Erreur lors de l\'envoi des données: ' + error);
                    console.error("Erreur AJAX:", status, error);
                }
            });
        });
    }

    loadItems('#trending-product-tab', '#trending-products-grid', 'trending-product-get', 'produits');
    handleItemClick('#trending-products-grid', '/products/trending-set');

    loadItems('#top-categories-tab', '#selectedCategories', 'categories-list', 'catégories');
    handleItemClick('#selectedCategories', '/categories/trending-set');

    $("#updateContact").on("click", function (e) {
        e.preventDefault();
        var contactTitle = $("#contactTitle").val().trim();
        var contactIntro = $("#contactIntro").val().trim();
        var contactAddress = $("#contactAddress").val().trim();
        var contactPhone = $("#contactPhone").val().trim();
        var contactEmail = $("#contactEmail").val().trim();
        var contactMap = $("#contactMap").val().trim();
        $.ajax({
            url: "/data-modify", type: "POST", data: { page: "contact", title: contactTitle, introduction: contactIntro, address: contactAddress, phone: contactPhone, email: contactEmail, map: contactMap }, dataType: "json", success: function (response) {
                if (response.success) {
                    showToaster(response.message);

                } else {
                    showToaster(response.message);

                }
            }, error: function (xhr, status, error) {
                console.error("Erreur AJAX :", error);
                showToaster("Une erreur est survenue. Veuillez réessayer.");

            }
        });
    });
    //Users

    // Fonction pour bloquer le bouton d'ajout de manager pendant la création
    $("#addManagerButon").click(function (e) {
        e.preventDefault();

        // Référence au bouton pour pouvoir le bloquer/débloquer
        const $button = $(this);

        // Bloquer le bouton et montrer l'état de chargement
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Création en cours...');

        var formData = {
            username: $("#managerName").val(),
            fullname: $("#managerFullname").val(),
            email: $("#managerEmail").val(),
            phone: $("#managerPhone").val(),
            password: $("#password").val(),
            repeatPassword: $("#repeatPassword").val(),
            csrf_token: csrf_token
        };

        // Validation des champs
        if (formData.username.trim() === "") {
            showToaster("Le nom d'utilisateur est requis.");
            resetButton();
            return;
        }

        var usernamePattern = /^[a-zA-Z0-9]([a-zA-Z0-9_-]*[a-zA-Z0-9])?$/;
        if (!usernamePattern.test(formData.username)) {
            showToaster("Le nom d'utilisateur doit être entre 3 et 20 caractères et ne peut contenir que des lettres, chiffres, tirets ou underscores.");
            resetButton();
            return;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(formData.email)) {
            showToaster("Veuillez entrer un email valide.");
            resetButton();
            return;
        }

        if (formData.phone.trim() !== "") {
            var phonePattern = /^[0-9]{10}$/;
            if (!phonePattern.test(formData.phone)) {
                showToaster("Veuillez entrer un numéro de téléphone valide.");
                resetButton();
                return;
            }
        }

        if (formData.password.length < 6) {
            showToaster("Le mot de passe doit contenir au moins 6 caractères.");
            resetButton();
            return;
        }

        if (formData.fullname.length < 6 || formData.fullname.length > 50) {
            showToaster("Le Nom complet est invalide (min.5 max.50 caractères alphanumériques).");
            resetButton();
            return;
        }

        if (formData.password !== formData.repeatPassword) {
            showToaster('Les mots de passe ne correspondent pas.');
            resetButton();
            return;
        }

        $.ajax({
            type: "POST",
            url: "/add-user",
            data: formData,
            success: function (response) {
                try {
                    var data = JSON.parse(response);
                    if (data.error) {
                        showToaster(data.error);
                        resetButton();
                    } else {
                        userData = {
                            'id': data.id,
                            'username': data.username,
                            'fullname': data.fullname,
                            'email': data.email,
                            'role': 'manager',
                            'status': 'actif',
                            'created_at': data.created_at
                        };
                        user = JSON.stringify(userData);
                        var newRow = `
                    <tr data-user-id="${data.id}">
                        <td><b>${data.fullname || ""}</b> (${data.username})</td>
                        <td>
                            <div class="role-container">
                                <select class="form-select manStatus-select" data-id="${data.id}" data-type="status">
                                    <option value="active">Actif</option>
                                    <option value="inactive" selected>Inactif</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="role-container">
                                <select class="form-select manType" data-id="${data.id}" data-type="role">
                                    <option value="super_manager">Super Manager</option>
                                    <option value="manager" selected>Manager</option>
                                </select>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info btn-sm view-btn" data-user='${user}'>
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <!-- Bouton Modifier -->
                                <button class="btn btn-outline-warning btn-sm manager-edit-btn" data-user='${user}'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- Bouton Supprimer -->
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-user-id="${data.id}" data-username="${data.username}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                        $('#usersTable tbody').append(newRow);
                        $('#addManagerModal').modal('hide');

                        // Réinitialiser le formulaire
                        $('#addManagerForm')[0].reset();

                        // Réinitialiser le bouton après succès
                        resetButton();

                        showToaster('Compte créé avec succès !');
                    }
                } catch (e) {
                    showToaster("La réponse du serveur n'est pas valide.");
                    resetButton();
                }
            },
            error: function () {
                showToaster("Erreur de communication avec le serveur.");
                resetButton();
            }
        });

        // Fonction pour réinitialiser l'état du bouton
        function resetButton() {
            $button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Créer le compte');
        }
    });
    $('.delete-manager').click(function () {

        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "manager");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", $(this).data('user-id'));
    });
    $('#usersTable').on('click', '.view-btn', function () {
        const id = $(this).closest('tr').data('user-id');
        $.ajax({
            type: "POST",
            url: "manager/get-infos",
            data: { id: id },
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    const userData = response.data;
                    const createdAtFormatted = formatTimeDifference(userData.created_at);

                    // Définir le rôle et ses styles
                    let role, roleBadgeClass;
                    switch (userData.role) {
                        case 'admin':
                            role = 'Administrateur';
                            roleBadgeClass = 'bg-danger';
                            break;
                        case 'super_manager':
                            role = 'Super Manager';
                            roleBadgeClass = 'bg-warning text-dark';
                            break;
                        default:
                            role = 'Manager';
                            roleBadgeClass = 'bg-info';
                            break;
                    }

                    // Définir le statut et ses styles
                    let statusClass;
                    if (userData.status === 'active') {
                        statusClass = 'bg-success';
                    } else {
                        statusClass = 'bg-secondary';
                    }

                    // Mise à jour des informations dans le modal
                    $('#viewUserId').text(userData.id);
                    $('#viewUsername').text(userData.username);
                    $('#viewFullname').text(userData.fullname || 'Non spécifié');
                    $('#viewEmail').text(userData.email);
                    $('#viewPhone').text(userData.phone || 'Non spécifié');
                    $('#viewRole').text(role).removeClass().addClass('badge rounded-pill px-3 py-2 ' + roleBadgeClass);
                    $('#userStatusBadge').removeClass().addClass('position-absolute badge rounded-pill ' + statusClass);
                    $('#viewCreatedAt').text(createdAtFormatted);
                    $("#manager-edit-btn").data('user-id', userData.id);

                    $('#viewUserModal').modal('show');
                } else {
                    showToaster("Aucune donnée pour l'utilisateur n'a été trouvée!");
                }
            }
        });
    });
    $(document).on('click', '.manager-edit-btn', function () {
        const id = $(this).data('user-id');
        $('#editUserId').val(id);
        $('#editUserModal').modal('show');
    });
    $('#saveChangesBtn').on('click', function () {
        const userId = $('#editUserId').val();
        const password = $('#editPassword').val();
        const repeatPassword = $('#editRepeatPassword').val();

        // Disable the button to prevent multiple submissions
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');

        if (!password && !repeatPassword) {
            showToaster('Aucune modification détectée.');
            $btn.prop('disabled', false).html('Enregistrer');
            return;
        }

        if (String(password).length > 0) {
            if (password !== repeatPassword) {
                showToaster('Les mots de passe ne correspondent pas.');
                $btn.prop('disabled', false).html('Enregistrer');
                return;
            }
            if (password.length < 6) {
                showToaster('Le mot de passe doit contenir au moins 6 caractères.');
                $btn.prop('disabled', false).html('Enregistrer');
                return;
            }
        }

        const $row = $(`tr[data-user-id="${userId}"]`);
        $.ajax({
            url: '/manager/update/password',
            type: 'POST',
            data: {
                id: userId,
                password: password,
                csrf_token: csrf_token
            },
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    userDataStore.id = userId;
                    setTimeout(() => {
                        $row.removeClass('highlight-row');
                    }, 2000);
                    $('#editUserModal').modal('hide');
                    showToaster(result.message);
                } else {
                    showToaster(result.message);
                }
                $btn.prop('disabled', false).html('Enregistrer les modifications');
            },
            error: function () {
                showToaster('Erreur de communication avec le serveur.');
                $btn.prop('disabled', false).html('Enregistrer les modifications');
            }
        });
    });

    $('#confirmDeleteBtn').on('click', function () {
        const userId = $(this).data('user-id');
        $.ajax({
            url: '/delete-user', type: 'POST', contentType: 'application/json', data: JSON.stringify({ id: userId }), success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    $(`tr[data-user-id="${userId}"]`).remove();
                    showToaster('Utilisateur supprimé avec succès.');
                } else {
                    showToaster('Erreur lors de la suppression',);

                }
            }, error: function () {
                showToaster('Erreur de communication avec le serveur.');

            }
        });
        $('#deleteModal').modal('hide');
    });
    $("#add-sections-btn").on('click', function () {
        $("#featuresContainer").empty();
        addOrEdit = 0;
    });
    $('.secTempBtn').on('mouseenter', function () {
        const modelId = $(this).data('id');
        const template = $(`.secTempView[data-id="${modelId}"]`);

        $('#secTempViewer').empty();
        if (template.length) {
            $('#secTempViewer').html(template.html());
        }
    });
    $(document).on('change', '.manStatus-select', function () {
        const userId = $(this).closest('tr').data('user-id');
        const newStatus = $(this).val();
        const $row = $(`tr[data-user-id="${userId}"]`);
        $.ajax({
            url: '/manager/update/status', type: 'POST', data: {
                id: userId, status: newStatus, csrf_token: csrf_token
            },
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    $row.find('td.status-cell').text(newStatus);
                    $row.addClass('highlight-row');
                    setTimeout(() => {
                        $row.removeClass('highlight-row');
                    }, 2000);
                    showToaster(result.message);
                } else {
                    showToaster(result.message);

                }
            }, error: function () {
                showToaster('Erreur de communication avec le serveur.');

            }
        });
    });
    $(document).on('change', '.manType', function () {
        const userId = $(this).closest('tr').data('user-id');
        const newStatus = $(this).val();
        const $row = $(`tr[data-user-id="${userId}"]`);
        $.ajax({
            url: '/manager/update/role', type: 'POST', data: {
                id: userId, status: newStatus, csrf_token: csrf_token
            },
            success: function (response) {
                const result = JSON.parse(response);
                showToaster(result.message);
            }, error: function () {
                showToaster('Erreur de communication avec le serveur.');
            }
        });
    });
    $('.secTempBtn').on('click', function () {
        // Réinitialiser l'état des boutons
        $('.secTempBtn').removeClass('active');
        $(this).addClass('active');
        $('#imagesContainer').html("");

        // Obtenir l'ID du modèle et la page
        currentModelId = $(this).data('id');

        // Fermer le modal actuel
        $('#SectionsTempleModal').modal('hide');

        // Ouvrir le formulaire correspondant
        $(`#sectionEditModal${currentModelId}`).modal('show');

        // Réinitialiser le formulaire
        $(`#sectionEditForm${currentModelId}`)[0].reset();
        sectionData = {
            model: currentModelId,
            page: currentPage,
            data: {}
        };

        // Pour le modèle 1, initialiser avec 2 listes vides
        if (currentModelId === 1) {
            // Réinitialiser les listes
            $('#listsContainer').empty();
            addNewList();
            addNewList();
        }
    });
    $('#sectionEditModal1').on('click', '.add-list', function () {
        addNewList();
    });
    $('#sectionEditModal1').on('click', '.remove-list', function () {
        const $lists = $('#listsContainer .card');

        // Garder au moins 2 listes
        if ($lists.length <= 2) {
            showToaster('Vous devez avoir au moins 2 listes.');
            return;
        }

        $(this).closest('.card').remove();
    });
    $('#sectionEditModal1').on('click', '.add-list-item', function () {
        const $listContainer = $(this).closest('.card-body').find('.list-items-container');
        addListItem($listContainer);
    });
    $('#sectionEditModal1').on('click', '.remove-item', function () {
        const $items = $(this).closest('.list-items-container').find('.list-group-item');

        // Garder au moins 2 éléments
        if ($items.length <= 2) {
            showToaster('Vous devez avoir au moins 2 éléments dans la liste.');
            return;
        }

        $(this).closest('.list-group-item').remove();
    });
    $('#sectionEditModal1').on('change', '.is-link-switch', function () {
        const $urlInput = $(this).closest('.list-group-item').find('.item-url');
        if ($(this).is(':checked')) {
            $urlInput.removeClass('d-none');
        } else {
            $urlInput.addClass('d-none').val('');
        }
    });
    $(document).on("click", ".customSectionD", function () {
        page = $(this).data("page")
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "customSection");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", $(this).data("id"));
    });
    $(document).on("click", '.customSectionEdit', function () {
        addOrEdit = 1;
        const sectionId = $(this).data("id");
        const modelId = $(this).data("model");
        currentPage = $(this).data("page");

        $.ajax({
            type: "POST",
            url: "/sections/get-single",
            data: {
                id: sectionId,
                page: currentPage
            },
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    const sectionDetails = response.data;

                    // Stocker l'ID de section pour l'édition
                    currentSectionId = sectionDetails.id;

                    // Charger les données dans le formulaire approprié
                    switch (parseInt(modelId)) {
                        case 1:
                            loadModel1EditForm(sectionDetails);
                            break;
                        case 2:
                            loadModel2EditForm(sectionDetails);
                            break;
                        case 3:
                            $("#featuresContainer").empty();
                            loadModel3EditForm(sectionDetails);
                            break;
                        case 4:
                            loadModel4EditForm(sectionDetails);
                            break;
                    }
                    $(`#sectionEditModal${modelId}`).modal('show');
                } else {
                    showToaster(response.message || "Erreur lors de la récupération des données");
                }
            },
            error: function () {
                showToaster("Erreur de connexion au serveur");
            }
        });
    });
    $(".custom-sections-tab").on("click", function () {
        currentPage = $(this).data('page');
        const sectionsList = $("#" + currentPage + "-sections-list");
        $.ajax({
            type: "POST",
            url: "/sections/get",
            data: {
                page: currentPage
            },
            success: function (response) {
                sectionsList.html("");
                response = JSON.parse(response);
                if (response.success) {
                    // Détermine si la page n'a qu'une seule section
                    const hasSingleSection = response.data.length === 1;

                    response.data.forEach(function (sect, index) {
                        let id = sect.id;
                        let page = sect.page;
                        let model = sect.model;
                        let modelText = "";
                        switch (Number(model)) {
                            case 1:
                                modelText = "Liste";
                                break;
                            case 2:
                                modelText = "Annonce";
                                break;
                            case 3:
                                modelText = "Sections";
                                break;
                            case 4:
                                modelText = "Galerie";
                                break;
                            case 5:
                                modelText = "Vide";
                                break;

                            default:
                                break;
                        }
                        // Déterminer si les boutons haut/bas doivent être désactivés
                        let isFirst = index === 0;
                        let isLast = index === response.data.length - 1;

                        // Gestion des classes et badges
                        let itemClass = "";
                        let headerBadge = "";
                        let footerBadge = "";

                        if (hasSingleSection) {
                            // Si section unique, elle est considérée comme footer
                            itemClass = "list-group-item-warning";
                            footerBadge = '<span class="badge bg-danger ms-2">FOOTER</span>';
                        } else {
                            // Sinon, appliquer les règles normales
                            if (isLast && currentPage == "home") {
                                itemClass = "list-group-item-warning";
                                footerBadge = '<span class="badge bg-danger ms-2">FOOTER</span>';
                            }

                            if (isFirst) {
                                itemClass = "list-group-item-info";
                                headerBadge = '<span class="badge bg-primary ms-2">HEADER</span>';
                            }
                        }

                        const listItem = `
                    <li class="list-group-item d-flex justify-content-between align-items-center ${itemClass}" data-id="${id}">
                        <div class="drag-handle me-2" style="cursor: grab;">
                            <i class="fas fa-grip-vertical text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">#${index + 1}</span>
                                <strong>ID: ${id}</strong>
                                <span class="ms-3">Model: <b>${modelText}</b></span>
                                ${headerBadge}
                                ${footerBadge}
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm me-2">
                            <button class="btn btn-outline-secondary ${isFirst ? 'disabled' : ''} section-move"
                                data-id="${id}" data-page="${page}" data-direction="up"
                                ${isFirst ? 'disabled' : ''}>
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button class="btn btn-outline-secondary ${isLast ? 'disabled' : ''} section-move"
                                data-id="${id}" data-page="${page}" data-direction="down"
                                ${isLast ? 'disabled' : ''}>
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary customSectionEdit"
                                data-id="${id}" data-page="${page}" data-model="${model}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger customSectionD"
                                data-id="${id}" data-page="${page}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </li>`;
                        sectionsList.append(listItem);
                    });
                    // Initialiser le tri par glisser-déposer
                    initSortable();
                } else {
                    showToaster(response.message);
                }
            }
        });
    });
    // Gestion des boutons de déplacement haut/bas
    $(document).on("click", ".section-move", function () {
        const id = $(this).data("id");
        const page = $(this).data("page");
        const direction = $(this).data("direction");

        $.ajax({
            type: "POST",
            url: "/sections/reorder",
            data: {
                id: id,
                page: page,
                direction: direction
            },
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    // Rafraîchir la liste des sections
                    $(".custom-sections-tab.active").trigger("click");
                    showToaster(response.message);
                } else {
                    showToaster(response.message);
                }
            },
            error: function () {
                showToaster("Erreur de connexion au serveur");
            }
        });
    });
    // Gestionnaire pour le bouton d'ajout d'élément
    $(document).on('click', '.add-feature', function () {
        addFeature();
    });
    // Gestionnaire pour le bouton d'ajout d'image
    $(document).on('click', '.add-image', function () {
        addImage();
    });
    // Gestionnaire pour la suppression d'un élément
    $(document).on('click', '.remove-feature', function () {
        $(this).closest('.feature-item').remove();
        updateFeatureNumbers();
    });
    // Gestionnaire pour la suppression d'une image
    $(document).on('click', '.remove-image', function () {
        $(this).closest('.image-item').remove();
        updateImageNumbers();
    });
    $('#sectionEditForm1').on('submit', function (e) {
        e.preventDefault();

        // Collecter les données des listes
        const lists = [];
        $('#listsContainer .card').each(function () {
            const $card = $(this);
            const title = $card.find('.list-title').val().trim();

            // Collecter les éléments de la liste
            const items = [];
            $card.find('.list-items-container .list-group-item').each(function () {
                const $item = $(this);
                const text = $item.find('.item-text').val().trim();
                const isLink = $item.find('.is-link-switch').is(':checked');
                const url = isLink ? $item.find('.item-url').val().trim() : '';

                items.push({
                    text: text,
                    isLink: isLink,
                    url: url
                });
            });

            lists.push({
                title: title,
                items: items
            });
        });

        // Préparer les données pour l'envoi
        const formData = {
            id: currentSectionId,
            model: currentModelId,
            page: currentPage,
            data: {
                lists: lists
            }
        };
        saveSection(formData);
    });
    $('#sectionEditForm2').on('submit', function (e) {
        e.preventDefault();

        // Collecter les données du formulaire
        const smallTitle = $('#model2SmallTitle').val().trim();
        const largeTitle = $('#model2LargeTitle').val().trim();
        const description = $('#model2Description').val().trim();
        const imageUrl = $('#model2ImageUrl').val().trim();
        const linkUrl = $('#model2LinkUrl').val().trim();
        const linkText = $('#model2LinkText').val().trim();

        // Déterminer si un lien est présent
        const hasLink = linkUrl !== '' && linkText !== '';

        // Préparer les données pour l'envoi
        const formData = {
            id: currentSectionId,
            model: currentModelId,
            page: currentPage,
            data: {
                smallTitle: smallTitle,
                largeTitle: largeTitle,
                description: description,
                imageUrl: imageUrl,
                hasLink: hasLink,
                linkUrl: linkUrl,
                linkText: linkText
            }
        };
        saveSection(formData);
    });
    $('#sectionEditForm3').on('submit', function (e) {
        e.preventDefault();
        // Récupérer le titre
        const title = $('#model3Title').val();

        // Collecter les données des éléments
        const features = [];
        $('#featuresContainer .feature-item').each(function () {
            const $this = $(this);
            features.push({
                icon: $this.find('.feature-icon').val(),
                title: $this.find('.feature-title').val(),
                description: $this.find('.feature-description').val()
            });
        });

        const data = {
            id: currentSectionId,
            model: currentModelId,
            page: currentPage,
            data: {
                title: title,
                features: features
            }
        }
        // Appeler la fonction de sauvegarde
        saveSection(data);
    });
    $('#sectionEditForm4').on('submit', function (e) {
        e.preventDefault();
        // Récupérer les données du formulaire
        const title = $('#model4Title').val();
        const description = $('#model4Description').val();

        // Collecter les données des images
        const images = [];
        $('#imagesContainer .image-item').each(function () {
            const $this = $(this);
            images.push({
                url: $this.find('.image-url').val(),
                caption: $this.find('.image-caption').val()
            });
        });

        const data = {
            id: currentSectionId,
            model: currentModelId,
            page: currentPage,
            data: {
                title: title,
                description: description,
                images: images
            }
        }
        // Appeler la fonction de sauvegarde
        saveSection(data);
    });
    $('#sectionEditForm5').on('submit', function (e) {
        e.preventDefault();
        const data = {
            id: currentSectionId,
            model: currentModelId,
            page: currentPage,
            data: {
                title: "Modele vide",
            }
        }
        // Appeler la fonction de sauvegarde
        saveSection(data);
    });
    function initSortable() {
        if (sortableList) {
            sortableList.destroy();
        }
        const sectionsList = document.getElementById(currentPage + '-sections-list');
        if (sectionsList) {
            sortableList = new Sortable(sectionsList, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const sectionIds = Array.from(evt.to.children).map(
                        item => $(item).data('id')
                    );
                    updateSectionsOrder(sectionIds);
                }
            });
        }
    }

    $('#sectionsManageTrigger').on('click', function () {
        $(".custom-sections-tab.active").trigger("click");
    });



    $("#showLog").on('click', function () {
        const logLevel = $("#logLevel").val().trim();
        const logSearch = $("#logSearch").val().trim();
        const logDateRange = $("#logDateRange").val().trim();

        window.open(
            '/log?level=' + encodeURIComponent(logLevel) +
            "&date=" + encodeURIComponent(logDateRange) +
            "&search=" + encodeURIComponent(logSearch),
            'LogWindow',
            'width=800,height=600,resizable=yes,scrollbars=yes'
        );
    });
    $('#delLog').on('click', function () {
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "log");
        $("#custom-toast").data("index", "delete");
        $('#custom-toast .toast-message').text("Voulez vous vraiment vider le log?")
    });

    $("#toast-confirm").on("click", function () {
        switch ($("#custom-toast").data("confirm")) {
            case "order":
                $.ajax({
                    url: "/orders/delete",
                    type: "POST",
                    data: {
                        order_id: currentOrderId
                    },
                    dataType: "json",
                    success: function (response) {
                        $("#orderDetailsModal").modal("hide");
                        $("#ordersModal").modal("show");
                        showToaster("Commande supprimée avec succès ");
                        // Recharger les commandes
                        loadOrders(ordersCurrentPage);
                    },
                    error: function () {
                        showToaster("Erreur lors de la suppression de la commande");
                    }
                });
                break;
            case "subscriber":
                $.ajax({
                    url: "/subscribers/delete",
                    type: "POST",
                    data: { id: $("#custom-toast").data("id") },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            loadSubscribersList();
                        }
                        showToaster(response.message)
                    }
                });
                break;
            case "customSection":
                $.ajax({
                    type: "post",
                    url: "/home/section-remove",
                    data: {
                        'id': $("#custom-toast").data("id"),
                        'page': page
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        $(".custom-sections-tab.active").trigger("click");
                        showToaster(response.message)
                    }
                });
                break;
            case "manager":
                const userId = $("#custom-toast").data("id");
                $.ajax({
                    url: '/user-delete', type: 'POST',
                    data: { id: parseInt(userId) },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $(`tr[data-user-id="${userId}"]`).remove();
                            $('#deleteModal').modal('hide');
                            showToaster('Manager supprimé avec succès');
                        } else {
                            showToaster(response.message || 'Erreur lors de la suppression de l\'utilisateur');

                        }
                    }, error: function () {
                        showToaster('Erreur de communication avec le serveur.');

                    }
                });
                break;
            case "article":
                $.ajax({
                    url: '/manager/articles/delete',
                    type: "POST",
                    data: {
                        article_id: $("#custom-toast").data("id")
                    },
                    dataType: "json",
                    success: function (response) {
                        response = typeof response === 'string' ? JSON.parse(response) : response;

                        if (response.success) {
                            showToaster(response.message || 'Article supprimé avec succès');
                            ArticleManager.loadArticles();
                        } else {
                            showToaster(response.message || 'Erreur lors de la suppression de l\'article');
                        }
                    },
                    error: function () {
                        showToaster('Erreur lors de la suppression de l\'article');
                    }
                });
                break;
            case "log":
                $.ajax({
                    url: '/log/del',
                    type: "POST",
                    success: function (response) {
                        response = typeof response === 'string' ? JSON.parse(response) : response;
                        if (response.success) {
                            showToaster(response.message || 'Log vidé avec succès');
                        } else {
                            showToaster(response.message || 'Erreur lors du vidage du log');
                        }
                    },
                    error: function () {
                        showToaster('Erreur lors du vidage du log');
                    }
                });
                break;
            default:
                break;
        }
        $("#custom-toast").fadeOut();
    });
});
function saveSection(data) {
    const url = addOrEdit ? '/sections/update' : '/sections/add';

    // Préparer les données pour l'envoi
    const sendData = {
        id: data.id,
        model: data.model,
        page: data.page,
        data: data.data
    };

    $.ajax({
        url: url,
        type: 'POST',
        data: sendData,
        success: function (response) {
            response = JSON.parse(response);
            if (response.success) {
                $(`#sectionEditModal${currentModelId}`).modal('hide');

                // Rafraîchir la liste des sections
                $(".custom-sections-tab.active").trigger("click");
                showToaster(response.message);
            } else {
                showToaster(response.message || "Une erreur est survenue");
            }
        },
        error: function (xhr, status, error) {
            showToaster(`Erreur lors de ${isEdit ? 'la modification' : "l'ajout"} de la section: ` + error);
        }
    });
}