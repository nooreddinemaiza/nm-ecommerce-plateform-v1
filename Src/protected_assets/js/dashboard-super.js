
// Variables globales
let allProductCs = [];
let selectedProductCIds = new Set();
let deselectedProductCIds = new Set();
let manualSelectionChanges = new Set(); // Pour suivre les changements manuels de l'utilisateur
let selectedCategory = null;
let categoryTitle = "";
$(document).ready(function () {
    $('#newCategoryBtn').click(function () {
        $('#newCategoryModal').modal('show');

    });
    $('#catModalTrig').on('click', function () {
        $.ajax({
            type: "post",
            url: "/categories-list",
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    $('#categoriesTableBody').html("");
                    response.data.forEach(cat => {
                        let id = cat.id;
                        let title = cat.title;
                        let description = cat.description;
                        let tags = cat.tags;
                        let reduction = cat.reduction;
                        $('#categoriesTableBody').append(`
                            <tr>
                                <td>${id}</td>
                                <td>${title}</td>
                                <td>${description}</td>
                                <td><span class="truncate">${tags}</span></td>
                                <td>${reduction || 0}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-danger btn-sm delete-category-btn" data-category-id="${id}">
                                            <i class="fa-solid fa-delete-left"></i>
                                        </button>
                                        <button class="btn btn-outline-info btn-sm view-category-details" data-category-id="${id}"">
                                        <i class="fas fa-eye me-1"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm edit-category-btn" data-category-id="${id}">
                                        <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm get-category-pr" data-category-id="${id}" data-category-title="${title}" data-category-description="${description}" data-bs-toggle="modal" data-bs-target="#catproductCModal" title="Gerer les produits de la catégorie" >
                                        <i class="fa fa-plus-square" aria-hidden="true"></i>
                                        </button>
                                    </div>   
                                </td>
                            </tr>`);
                    });
                }
            }
        });
    });
    $('#addCategoryBtn').click(function () {
        $('#addCategoryBtn').prop('disabled', false);
        const title = $('#categoryTitle').val().trim();
        const description = $('#categoryDescription').val().trim();
        const tags = $('#categoryTags').val().trim();
        const reduction = $('#categoryReduction').val().trim();
        const image = $('#categoryImage')[0].files[0];
        if (title === '') {
            showToaster("Le titre est requis!");
            return;

        } const categoryData = new FormData();
        categoryData.append('title', title);
        categoryData.append('description', description);
        categoryData.append('tags', tags);
        categoryData.append('reduction', reduction || 0);
        if (image) {
            categoryData.append('image', image);

        } $.ajax({
            url: '/categories/create',
            type: 'POST',
            data: categoryData,
            processData: false,
            contentType: false,
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    id = response.id;
                    $('#newCategoryModal').modal('hide');
                    $('#categoriesTableBody').append(`
                        <tr>
                            <td>${id}</td>
                            <td>${title}</td>
                            <td>${description}</td>
                            <td><span class="truncate">${tags}</span></td>
                            <td>${reduction || 0}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-danger btn-sm delete-category-btn" data-category-id="${response.id}" data-category-title="${title}">
                                            <i class="fa-solid fa-delete-left"></i>
                                        </button>
                                        <button class="btn btn-outline-info btn-sm view-category-details" data-category-id="${response.id}" data-category-title="${title}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm edit-category-btn" data-category-id="${response.id}" data-category-title="${title}" data-category-description="${description}" data-category-tags="${tags}" data-category-reduction="${reduction || 0}">
                                        <i class="fa-solid fa-edit"></i>
                                        </button>
                                    </div>   
                            </td>
                        </tr>`);
                    showToaster("Catégorie ajoutée avec succès !");
                } else {
                    if (response.error == "cat_found") {
                        showToaster("La catégorie existe déjà !");

                    } else showToaster("Erreur lors de l'ajout de la catégorie.");
                }
            }, error: function () {
                showToaster("Erreur lors de l'ajout de la catégorie.");
            },
            complete: function () {
                $('#addCategoryBtn').prop('disabled', false);
            }
        });
    });
    $(document).on('click', '.edit-category-btn', function () {
        const id = $(this).data('category-id');
        $.ajax({
            type: "POST",
            url: "get-category", // ton endpoint PHP ou route backend
            data: { id: id },
            dataType: "json", // pour s'assurer que la réponse est bien interprétée
            success: function (response) {
                if (!response.success) {
                    showToaster("Catégorie introuvable.");
                    return;
                }
                category = response.data;
                title = category.title;
                description = category.description;
                tags = category.tags;
                reduction = category.reduction;
                $('#editCategoryTitle').val(title);
                $('#editCategoryDescription').val(description);
                $('#editCategoryTags').val(tags);
                $('#editCategoryReduction').val(reduction);

                $("#mangeCategoriesModal").modal('hide');
                $("#shcategoryDetailsModal").modal('hide');
                $("#editCategoryModal").modal('show');
            }
        });

    });
    $('#editCategoryBtn').on('click', function () {
        $('#editCategoryBtn').prop('disabled', true);
        let title = $('#editCategoryTitle').val();
        let description = $('#editCategoryDescription').val();
        let tags = $('#editCategoryTags').val();
        let reduction = $('#editCategoryReduction').val();
        if (title == '') {
            showToaster('Le titre ne peut pas etre vide!');
            return;
        }
        if (category.title != title) {
            category.title = title
        }
        if (category.description != description) {
            category.description = description
        }
        if (category.tags != tags) {
            category.tags = tags
        }
        if (category.reduction != reduction) {
            category.reduction = reduction
        }
        $.ajax({
            type: "post",
            url: "/categories-edit",
            data: category,
            success: function (response) {
                response = JSON.parse(response);
                showToaster(response.message);
                if (response.success) {
                    $('#editCategoryModal').modal('hide');
                }
            },
            complete: function () {
                $("#shcategoryDetailsModal").modal('show');
                setTimeout(() => {
                    $('#editCategoryBtn').prop('disabled', false);
                }, 2000);
            }
        });
    });
    $(document).on('click', '.delete-category-btn', function () {
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "category");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", $(this).data('category-id'));
    });
    $(document).on('click', '.view-category-details', function () {
        const id = $(this).data('category-id');
        $.ajax({
            type: "POST",
            url: "get-category", // ton endpoint PHP ou route backend
            data: { id: id },
            dataType: "json", // pour s'assurer que la réponse est bien interprétée
            success: function (response) {
                if (!response.success) {
                    showToaster("Catégorie introuvable.");
                    return;
                }
                response = response.data;
                // Mettre à jour les champs
                $("#shcategoryId").text(response.id);
                $("#shcategoryTitle").text(response.title);
                $('#delete-category-btn').data('category-id', response.id);
                $('#get-category-pr').data('category-id', response.id);
                $('#edit-category-btn').data('category-id', response.id);

                // Description
                if (response.description) {
                    $("#shcategoryDescription").text(response.description).parent().show();
                } else {
                    $("#shcategoryDescription").text("Aucune description disponible.").parent().show();
                }

                // Tags
                let tagsHtml = '';
                if (response.tags) {
                    const tagsArray = response.tags.split(',');
                    tagsArray.forEach(tag => {
                        tagsHtml += `<span class="shcategory-badge"><i class="fas fa-hashtag"></i> ${tag.trim()}</span> `;
                    });
                }
                $("#shcategoryTagsContainer").html(tagsHtml);

                // Date
                if (response.created_at) {
                    const date = new Date(response.created_at);
                    const formattedDate = date.toLocaleDateString('fr-FR');
                    $("#shcategoryDate").text(formattedDate);
                }

                // Trend badge
                if (response.is_trend == 1 || response.is_trend === true) {
                    $("#trendBadgeContainer").html(`<span class="badge bg-warning text-dark"><i class="fas fa-fire me-1"></i> Tendance</span>`).show();
                } else {
                    $("#trendBadgeContainer").hide();
                }

                // Reduction badge
                if (response.reduction && parseFloat(response.reduction) > 0) {
                    $("#reductionBadgeContainer").html(`<span class="badge bg-success"><i class="fas fa-percentage me-1"></i> Réduction : ${response.reduction}%</span>`).show();
                } else {
                    $("#reductionBadgeContainer").hide();
                }

                // Statistiques
                $("#productCount").text(response.product_count ?? 0);
                $("#visitesCount").text(response.visites ?? 0);

                // Lien
                if (response.link) {
                    $("#shcategoryLink").attr('href', response.link).text('Visiter');
                } else {
                    $("#shcategoryLink").removeAttr('href').text("Aucun lien");
                }

                // Image
                if (response.image) {
                    $("#shcategoryImage").attr('src', response.image);
                } else {
                    $("#shcategoryImage").attr('src', '/api/placeholder/400/320');
                }

                // Afficher le modal
                $('#shcategoryDetailsModal').modal('show');
            },
            error: function () {
                showToaster("Erreur lors de la récupération des données de la catégorie.");
            }
        });
    });
    // Chargement des produits lors de l'ouverture du modal
    $(document).on('click', '.get-category-pr', function () {
        const id = $(this).data('category-id');
        const title = $(this).data('category-title') || "Catégorie sélectionnée";

        selectedCategory = id;
        categoryTitle = title;

        $('#shcategoryDetailsModal').modal('hide');
        // Réinitialiser l'état
        selectedProductCIds.clear();
        deselectedProductCIds.clear();
        manualSelectionChanges.clear();

        $('#selectAllProductCs').prop('checked', false);

        // Mettre à jour le titre du modal
        $('#catproductCModalLabel').html(`<i class="fas fa-box"></i> Gestion des produits - ${categoryTitle}`);

        loadCategoryProducts(id);
    });
    // Chargement des produits
    function loadCategoryProducts(categoryId) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/get-category-products",
            data: {
                id: categoryId
            },
            success: function (response) {
                if (response.success) {
                    allProductCs = response.data;

                    // Prétraitement des produits pour identifier ceux qui ont déjà la catégorie
                    allProductCs.forEach(product => {
                        // Vérifier si le produit a déjà la catégorie sélectionnée
                        product.hasCategory = false;

                        // Vérifier si categories existe et est un tableau
                        if (product.categories && Array.isArray(product.categories)) {
                            product.hasCategory = product.categories.some(cat =>
                                parseInt(cat.category_id) === parseInt(selectedCategory)
                            );
                        }

                        // Présélectionner les produits qui ont déjà la catégorie
                        if (product.hasCategory) {
                            selectedProductCIds.add(product.product_id);
                        }
                    });

                    displayProductCscat(allProductCs);
                    $('#totalProductCs').text(allProductCs.length + ' total');
                } else {
                    $('#productCListContainer').html('<div class="alert alert-danger">Erreur lors du chargement des produits</div>');
                }
            },
            error: function () {
                $('#productCListContainer').html('<div class="alert alert-danger">Erreur lors du chargement des produits</div>');
            }
        });
    }

    $(document).on('click', '#saveChangesBtn', function () {
        const productsToAdd = Array.from(selectedProductCIds)
            .filter(id => {
                const productEl = $(`.productC-item[data-id="${id}"]`);
                return productEl.length && !productEl.data('has-category');
            })
            .map(id => ({
                "product_id": id
            }));

        const productsToRemove = Array.from(deselectedProductCIds)
            .map(id => ({
                "product_id": id,
                "category_id": selectedCategory
            }));

        const payload = {
            selected_products: productsToAdd,
            deselected_products: productsToRemove,
            category_id: selectedCategory
        };

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "add-products-to",
            data: payload,
            success: function (response) {
                if (response.success) {
                    let messages = [];
                    let type = 'info';

                    if (response.operations?.add?.success) {
                        const count = response.operations.add.added_products.length;
                        messages.push(`✅ ${count} produit(s) ajouté(s) à la catégorie.`);
                        type = 'success';
                    }

                    if (response.operations?.add?.skipped_products?.length) {
                        messages.push(`ℹ️ ${response.operations.add.skipped_products.length} produit(s) déjà présent(s) ignoré(s).`);
                        if (type !== 'success') type = 'secondary';
                    }

                    if (response.operations?.remove?.success) {
                        const count = response.operations.remove.removed_products.length;
                        messages.push(`🗑️ ${count} produit(s) retiré(s) de la catégorie.`);
                        type = 'warning';
                    }

                    if (messages.length === 0) {
                        messages.push("Aucune modification n'a été apportée.");
                        type = 'secondary';
                    }

                    showToaster(messages.join("<br>"), type);
                    $('#catproductCModal').modal('hide');
                } else {
                    const msg = response.message || 'Erreur lors de l\'enregistrement des modifications';
                    showToaster(`❌ ${msg}`, 'danger');
                }
            },
            error: function () {
                showToaster('❌ Erreur lors de la communication avec le serveur.', 'danger');
            }
        });
    });

    // Recherche de produits
    $(document).on('input', '#productCSearch', function () {
        const searchTerm = $(this).val().toLowerCase();
        const statusFilter = $('#statusFilter').val();
        filterProductCs(searchTerm, statusFilter);
    });

    // Filtre par statut
    $(document).on('change', '#statusFilter', function () {
        const searchTerm = $('#productCSearch').val().toLowerCase();
        const statusFilter = $(this).val();
        filterProductCs(searchTerm, statusFilter);
    });

    // Tri des produits
    $(document).on('change', '#sortProductCs', function () {
        const sortValue = $(this).val();
        sortProductCList(sortValue);
    });

    // Fonction de filtrage des produits
    function filterProductCs(searchTerm, statusFilter) {
        let filteredProductCs = allProductCs.filter(productC => {
            const matchesSearch = productC.product_title.toLowerCase().includes(searchTerm) ||
                (productC.product_description && productC.product_description.toLowerCase().includes(searchTerm));
            const matchesStatus = statusFilter === 'all' || productC.product_status === statusFilter;

            return matchesSearch && matchesStatus;
        });

        // Appliquer le tri actuel aux résultats filtrés
        const currentSort = $('#sortProductCs').val();
        let sortedFilteredProductCs = sortProductCs(filteredProductCs, currentSort);

        displayProductCscat(sortedFilteredProductCs);
        $('#totalProductCs').text(filteredProductCs.length + ' total');
    }

    // Fonction de tri
    function sortProductCList(sortValue) {
        const searchTerm = $('#productCSearch').val().toLowerCase();
        const statusFilter = $('#statusFilter').val();

        // Filtrer d'abord
        let filteredProductCs = allProductCs.filter(productC => {
            const matchesSearch = productC.product_title.toLowerCase().includes(searchTerm) ||
                (productC.product_description && productC.product_description.toLowerCase().includes(searchTerm));
            const matchesStatus = statusFilter === 'all' || productC.product_status === statusFilter;

            return matchesSearch && matchesStatus;
        });

        // Puis trier
        let sortedProductCs = sortProductCs(filteredProductCs, sortValue);

        displayProductCscat(sortedProductCs);
    }

    // Fonction qui effectue le tri
    function sortProductCs(productCs, sortValue) {
        let sortedProductCs = [...productCs];

        switch (sortValue) {
            case 'title-asc':
                sortedProductCs.sort((a, b) => a.product_title.localeCompare(b.product_title));
                break;
            case 'title-desc':
                sortedProductCs.sort((a, b) => b.product_title.localeCompare(a.product_title));
                break;
            case 'price-asc':
                sortedProductCs.sort((a, b) => parseFloat(a.product_price) - parseFloat(b.product_price));
                break;
            case 'price-desc':
                sortedProductCs.sort((a, b) => parseFloat(b.product_price) - parseFloat(a.product_price));
                break;
            case 'cat-first':
                // Produits avec la catégorie sélectionnée d'abord
                sortedProductCs.sort((a, b) => {
                    if (a.hasCategory && !b.hasCategory) return -1;
                    if (!a.hasCategory && b.hasCategory) return 1;
                    return a.product_title.localeCompare(b.product_title);
                });
                break;
            case 'no-cat-first':
                // Produits sans la catégorie sélectionnée d'abord
                sortedProductCs.sort((a, b) => {
                    if (!a.hasCategory && b.hasCategory) return -1;
                    if (a.hasCategory && !b.hasCategory) return 1;
                    return a.product_title.localeCompare(b.product_title);
                });
                break;
        }

        return sortedProductCs;
    }

    // Sélection de tous les produits visibles
    $(document).on('change', '#selectAllProductCs', function () {
        const isChecked = $(this).prop('checked');

        $('.productC-checkbox').each(function () {
            const productId = $(this).val();
            const hasCategory = $(this).closest('.productC-item').data('has-category');

            if (isChecked) {
                // Ajouter à la sélection, sauf si déjà désélectionné manuellement
                if (!manualSelectionChanges.has(productId) || hasCategory) {
                    selectedProductCIds.add(productId);
                    if (deselectedProductCIds.has(productId)) {
                        deselectedProductCIds.delete(productId);
                    }
                }
            } else {
                // Enlever de la sélection mais garder une trace si c'était auto-sélectionné
                if (hasCategory) {
                    deselectedProductCIds.add(productId);
                    manualSelectionChanges.add(productId);
                }
                selectedProductCIds.delete(productId);
            }

            $(this).prop('checked', isChecked);
        });

        updateSelectionCounters();
    });
    // Afficher/masquer les détails du produit
    $(document).on('click', '.productC-details-btn', function () {
        $(this).closest('.productC-item').find('.productC-details').toggleClass('d-none');
    });

    // Gestion de la sélection individuelle
    $(document).on('change', '.productC-checkbox', function () {
        const productId = $(this).val();
        const isChecked = $(this).prop('checked');
        const hasCategory = $(this).closest('.productC-item').data('has-category') === true;

        // Marquer ce produit comme ayant été modifié manuellement
        manualSelectionChanges.add(productId);

        if (isChecked) {
            selectedProductCIds.add(productId);
            deselectedProductCIds.delete(productId);
        } else {
            selectedProductCIds.delete(productId);
            // Si le produit a déjà la catégorie et est maintenant décoché,
            // l'ajouter à la liste de désélection
            if (hasCategory) {
                deselectedProductCIds.add(productId);
            }
        }

        // Vérifier l'état de "Tout sélectionner"
        updateSelectAllCheckboxState();
        updateSelectionCounters();
    });

    // Mise à jour de l'état du checkbox "Tout sélectionner"
    function updateSelectAllCheckboxState() {
        const visibleCheckboxes = $('.productC-checkbox').length;
        const checkedCheckboxes = $('.productC-checkbox:checked').length;

        $('#selectAllProductCs').prop({
            'checked': visibleCheckboxes > 0 && visibleCheckboxes === checkedCheckboxes,
            'indeterminate': checkedCheckboxes > 0 && checkedCheckboxes < visibleCheckboxes
        });
    }

    // Mise à jour des compteurs de sélection
    function updateSelectionCounters() {
        const selectedCount = selectedProductCIds.size;
        const deselectedCount = deselectedProductCIds.size;

        $('#countSelected').text(selectedCount + ' sélectionné(s)');
        $('#countDeselected').text(deselectedCount + ' désélectionné(s)');

        updateSelectionSummary();
    }

    // Mise à jour du résumé de sélection
    function updateSelectionSummary() {
        const toAdd = Array.from(selectedProductCIds).filter(id => {
            const productEl = $(`.productC-item[data-id="${id}"]`);
            return productEl.length && !productEl.data('has-category');
        }).length;

        const toRemove = deselectedProductCIds.size;

        let summaryText = '';
        if (toAdd > 0) {
            summaryText += `<span class="text-success">+${toAdd} à ajouter</span>`;
        }
        if (toRemove > 0) {
            if (summaryText) summaryText += ' | ';
            summaryText += `<span class="text-danger">-${toRemove} à retirer</span>`;
        }

        $('#selectionSummary').html(summaryText || 'Aucune modification');
    }

    function displayProductCscat(productCs) {
        if (!productCs || productCs.length === 0) {
            $('#productCListContainer').html('<div class="alert alert-warning">Aucun produit disponible</div>');
            return;
        }

        let html = '';
        productCs.forEach(function (productC) {
            // Déterminer si le produit est dans la catégorie active
            let hasCurrentCategory = productC.hasCategory;

            // Déterminer la classe de statut pour le style
            let statusClass = productC.product_status === 'affiche' ? 'border-success' : 'border-warning opacity-75';
            let categoryClass = hasCurrentCategory ? 'with-category already-associated' : 'no-category';

            let statusBadge = productC.product_status === 'affiche' ?
                '<span class="badge bg-success">Affiché</span>' :
                '<span class="badge bg-warning text-dark">Réduit</span>';

            // Badge pour indiquer si le produit est déjà associé à la catégorie
            let associationBadge = hasCurrentCategory ?
                '<span class="badge bg-info ms-2">déjà associé</span>' : '';

            // Rendre les catégories avec style
            let categoriesHtml = '';
            let categoriesArray = [];

            // Traiter les catégories si elles existent
            if (productC.categories && Array.isArray(productC.categories)) {
                productC.categories.forEach(cat => {
                    let isCurrentCategory = cat.category_id === selectedCategory ||
                        cat.category_id === parseInt(selectedCategory);
                    categoriesArray.push(
                        `<span class="category-tag ${isCurrentCategory ? 'current' : ''}">${cat.category_title}</span>`
                    );
                });
            }

            if (categoriesArray.length > 0) {
                categoriesHtml = categoriesArray.join(' ');
            } else {
                categoriesHtml = '<em class="text-muted">Aucune catégorie</em>';
            }

            // Déterminer si la case est cochée, basé sur l'état de sélection actuel
            let isChecked = selectedProductCIds.has(productC.product_id.toString()) ||
                selectedProductCIds.has(parseInt(productC.product_id));

            // Si le produit a déjà été désélectionné manuellement, ne pas le cocher
            if (deselectedProductCIds.has(productC.product_id.toString()) ||
                deselectedProductCIds.has(parseInt(productC.product_id))) {
                isChecked = false;
            }

            // Déterminer si la case doit être désactivée (pour les produits déjà associés, optionnel)
            let disabledClass = hasCurrentCategory ? 'checkbox-disabled' : '';

            html += `
            <div class="productC-item mb-2 p-2 border rounded ${statusClass} ${categoryClass}"
                data-id="${productC.product_id}"
                data-status="${productC.product_status}"
                data-title="${productC.product_title}"
                data-price="${productC.product_price}"
                data-has-category="${hasCurrentCategory}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input productC-checkbox"
                            type="checkbox"
                            value="${productC.product_id}"
                            id="productC-${productC.product_id}"
                            ${isChecked ? 'checked' : ''}
                            data-is-auto="${hasCurrentCategory}">
                            <label class="form-check-label w-100" for="productC-${productC.product_id}">
                                <span class="productC-title fw-bold">${productC.product_title}</span>
                                <div class="mt-1">
                                    <strong>Catégories:</strong>
                                    <div class="mt-1">${categoriesHtml}</div>
                                </div>
                            </label>
                    </div>
                    <div>
                        ${statusBadge}
                        ${associationBadge}
                        <button class="btn btn-sm btn-outline-info ms-2 productC-details-btn"
                            data-bs-toggle="tooltip"
                            title="Voir détails">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="productC-details mt-2 d-none">
                    <div class="card">
                        <div class="card-body small">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> ${productC.product_id}</p>
                                    <p><strong>Prix:</strong> ${productC.product_price} DH</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Statut:</strong> ${productC.product_status}</p>
                                    <p><strong>Associé à cette catégorie:</strong> ${hasCurrentCategory ? 'Oui' : 'Non'}</p>
                                </div>
                            </div>
                            <p><strong>Description:</strong> ${productC.product_description || "Pas de description!"}</p>
                        </div>
                    </div>
                </div>
            </div>
            `;
        });

        $('#productCListContainer').html(html);

        // Initialiser les tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
    // Réinitialiser le modal de notification lorsqu'il est fermé
    $('#notificationModal').on('hidden.bs.modal', function () {
        $('#notificationStatus').addClass('d-none').removeClass('alert-success alert-danger alert-info alert-warning');
        $('#subsNotificationMsg').val('');
        $('#sendNotificationBtn').prop('disabled', false);
    });
    // Ouvrir le modal et charger les données
    $("#noSubsModal").on("show.bs.modal", function () {
        loadSubscribersList();
    });
    // Sélectionner / désélectionner toutes les cases
    $("#select-all").on("change", function () {
        $(".select-row").prop("checked", $(this).prop("checked"));
    });
    // Suppression individuelle
    $(document).on("click", ".delete-btn", function () {
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "subscriber");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", $(this).data("id"));
    });
    // Suppression groupée
    $("#delete-selected").on("click", function () {
        let selectedIds = $(".select-row:checked").map(function () {
            return $(this).data("id");
        }).get();
        if (selectedIds.length === 0) {
            showToaster("Aucun utilisateur sélectionné !");
            return;
        }
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "subscriber");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", selectedIds);
    });
    $("#ordersListModalTrigger").click(() => loadOrders(ordersCurrentPage));
    $("#btnConfirmStatus").click(function () {
        const newStatus = $("#newStatusSelect").val();
        $.ajax({
            url: "/orders/update-status",
            type: "POST",
            data: {
                order_id: currentOrderId + ORDER_ID_NUMBER,
                status: newStatus
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#statusToast").modal('hide');
                    let status = orderStatusSwitcher(newStatus);
                    const orderRow = $(`tr[data - order - id= "${currentOrderId}"]`);
                    const statIcon = `
        < button class="btn btn-sm orderStatusIcon" data - order="${currentOrderId}" id = "orderStatusIcon" title = "Changer le statut" >
            <i class="fa-solid fa-rotate"></i>
                    </button > `;
                    orderRow.find(".order-status").html(status.statusText + statIcon).removeClass().addClass(`order - status ${status.statusClass} `);
                    $("#orderStatus").text(newStatus);
                    for (let i = 0; i < ordersData.length; i++) {
                        if (ordersData[i].client.order_id === currentOrderId) {
                            ordersData[i].client.status = newStatus;
                            break;
                        }
                    }
                }
                showToaster(response.message);
            },
            error: function () {
                showToaster("Erreur lors de la mise à jour du statut.", "danger");
            }
        });
    });
    $("#orderPrevPage").click(() => {
        if (ordersCurrentPage > 1) loadOrders(ordersCurrentPage - 1);
    });
    $("#orderNextPage").click(() => loadOrders(ordersCurrentPage + 1));
    $("#searchButton").click(() => loadOrders(1)); // Retour à la page 1 pour la recherche
    $("#searchOrders").on("keyup", function (e) {
        if (e.key === "Enter") {
            loadOrders(1);
        }
    });
    $("#statusFilter").change(() => loadOrders(1)); // Retour à la page 1 pour le filtre
    $("#ordersNewOnly").change(() => loadOrders(1)); // Retour à la page 1 pour le filtre
    $(document).on("click", ".btn-view-order", function () {
        const orderIndex = $(this).data("order-index");
        const order = ordersData[orderIndex];
        let orderStat = orderStatusSwitcher(order.client.status);
        currentOrderId = order.client.order_id;

        $("#orderClientName").text(order.client.nom_prenom);
        $("#orderClientAddress").text(order.client.address);
        $("#orderClientPhone").text(order.client.phone);
        $("#orderClientEmail").text(order.client.email);
        $("#orderId").text(order.client.order_id + ORDER_ID_NUMBER);
        $("#orderDate").text(order.client.date);
        $("#orderStatus").text(orderStat.statusText).addClass(orderStat.statusClass);

        // Tableau des produits
        $("#orderProductsTableBody").html("");
        let total = 0;
        order.produits.forEach(product => {
            let reducedPrice = product.unit_price;
            let reductionText = '';
            if (product.quantity >= product.appReduction.plus && product.appReduction.reduction > 0) {
                reductionText = ` < span class="text-danger" > (- ${product.appReduction.reduction}%)</span > `
                reducedPrice -= product.unit_price * product.appReduction.reduction / 100;
            }
            const row = `
    < tr >
                <td>${product.title}</td>
                <td>${product.unit_price} ${reductionText}</td>
                <td>${product.quantity}</td>
                <td>${reducedPrice * product.quantity}</td>
            </tr >
    `;
            $("#orderProductsTableBody").append(row);
            reductionText = ''
            total += reducedPrice * product.quantity;
        });
        $("#orderTotal").text(total);

        // Sélectionner le statut actuel dans la liste déroulante
        $("#newStatusSelect").val(order.client.status);

        // Fermer le modal des commandes et ouvrir celui des détails
        $("#ordersModal").modal("hide");
        $("#orderDetailsModal").modal("show");
    });
    $("#ordersSortBtn").click(function () {
        if (ordersNewOnly == 1) {
            $(this).html('<i class="fa fa-chevron-up"></i>');
            ordersNewOnly = 0;
        } else {
            $(this).html('<i class="fa fa-chevron-down"></i>');
            ordersNewOnly = 1;
        }
        loadOrders(ordersCurrentPage);
    });
    // Gestion du changement de statut
    $("#btnChangeStatus").click(function () {
        $("#statusToast").modal("show");
    });
    $(document).on("click", ".orderStatusIcon", function () {
        currentOrderId = $(this).data("order");
        $("#statusToast").modal("show");
    });
    // Gestion de la suppression de commande
    $(document).on("click", ".btn-delete-order", function () {
        currentOrderId = ($(this).data('order-index'));
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "order");
        $("#custom-toast").data("index", "delete");
    });
    $(document).mouseup(function (e) {
        let $toast = $("#custom-toast");
        // Vérifie si le clic est en dehors du toast
        if (!$toast.is(e.target) && $toast.has(e.target).length === 0) {
            $toast.fadeOut(300); // Cache le toast avec une animation de fondu
        }
    });
    // Gestion de l'envoi de notification
    $('#sendNotificationBtn').click(function () {
        const message = $('#subsNotificationMsg').val();
        if (message == '') {
            $('#notificationStatus')
                .removeClass('d-none alert-success alert-danger')
                .addClass('alert-warning')
                .text('Veuillez saisir un message avant d\'envoyer la notification.');
            return;
        }

        // Affichage de l'état d'envoi
        $('#notificationStatus')
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-info')
            .html('<i class="fas fa-spinner fa-spin me-2"></i>Envoi des notifications en cours...');

        // Désactiver le bouton pendant l'envoi
        $('#sendNotificationBtn').prop('disabled', true);
        // Requête AJAX vers /subscribers/notify
        $.ajax({
            url: '/subscribers/notify',
            type: 'POST',
            data: {
                message: message,
                emails: selectedEmails
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showToaster(response.message);
                    $('#notificationModal').modal('hide');
                } else {
                    $('#notificationStatus')
                        .removeClass('alert-info alert-success alert-warning')
                        .addClass('alert-danger')
                        .html('<i class="fas fa-exclamation-circle me-2"></i>' + (response.message || 'Une erreur est survenue.'));
                }
            },
            error: function () {
                $('#notificationStatus')
                    .removeClass('alert-info alert-success alert-warning')
                    .addClass('alert-danger')
                    .html('<i class="fas fa-exclamation-triangle me-2"></i>Erreur de connexion au serveur.');
            },
            complete: function () {
                // Réactiver le bouton une fois l'opération terminée
                $('#sendNotificationBtn').prop('disabled', false);
            }
        });
    });
    // Lier le bouton d'ouverture du modal d'email avec son modal
    $("#btnSendEmail").on("click", function () {
        const email = $("#orderClientEmail").text();
        $("#emailRecipient").val(email);
        $("#sendEmailModal").modal("show");
    });
    // Validation et envoi des informations du client
    $("#messageClient").click(function () {
        $("#messageClient").prop("disabled", true);
        const recipient = $("#emailRecipient").val();
        const subject = $("#emailSubject").val();
        const body = $("#emailBody").val();

        // Validation des champs
        if (!recipient || !subject || !body) {
            showToaster("Veuillez remplir tous les champs.", "warning");
            return;
        }

        if (!validateEmail(recipient)) {
            showToaster("Veuillez entrer une adresse email valide.", "warning");
            return;
        }

        // Envoi AJAX
        $.ajax({
            url: "/orders/message-client",
            type: "POST",
            data: {
                recipient: recipient,
                subject: subject,
                body: body
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#sendEmailModal").modal("hide");
                    showToaster("Message envoyé avec succès !", "success");
                } else {
                    showToaster("Erreur lors de l'envoi du message.", "danger");
                }
            },
            error: function () {
                showToaster("Erreur lors de l'envoi du message.", "danger");
            }
        });

    });
    // Ouvrir le modal d'envoi d'email
    $("#btnSendEmail").click(function () {
        $("#messageClient").prop("disabled", false);
        const email = $("#orderClientEmail").text();
        $("#emailRecipient").val(email);
        $("#sendEmailModal").modal("show");
    });
    // Notification individuelle
    $(document).on("click", ".notify-sub", function () {
        $("#messageClient").prop("disabled", false);
        let email = $(this).data("email");
        $("#emailRecipient").val(email);
        $("#noSubsModal").modal("hide");
        $("#sendEmailModal").modal("show");
    });
    // Notification groupée
    $("#notify-selected").on("click", function () {
        let selectedEmail = $(".select-row:checked").map(function () {
            return $(this).data("email");
        }).get();

        if (selectedEmail.length === 0) {
            showToaster("Aucun utilisateur sélectionné !");
            return;
        }
        $("#noSubsModal").modal("hide");
        selectedEmails = selectedEmail;
        $("#notificationModal").modal("show");
    });
    // Gestion de l'envoi de l'email
    $("#btnSendEmailConfirm").click(function () {
        const recipient = $("#emailRecipient").val();
        const subject = $("#emailSubject").val();
        const body = $("#emailBody").val();

        if (recipient && subject && body) {
            switch ($("#btnSendEmail").data("index")) {
                case "order":
                    $.ajax({
                        url: "/orders/send-email",
                        type: "POST",
                        data: {
                            recipient: recipient,
                            subject: subject,
                            body: body
                        },
                        dataType: "json",
                        success: function (response) {
                            $("#sendEmailModal").modal("hide");
                            showToaster("Email envoyé avec succès !", "success");
                        },
                        error: function () {
                            showToaster("Erreur lors de l'envoi de l'email.", "danger");
                        }
                    });
                    break;
                case "subscriber":
                    const subscriberEmail = $(this).data("email");
                    $("#emailRecipient").val(subscriberEmail);
                    break;
            }
        } else {
            showToaster("Veuillez remplir tous les champs.", "warning");
        }
    });
    $('#searchOrders2').on('input', function () {
        page = 1;
        filterAndRender();
    });
    $('#filterPeriod2').on('change', function () {
        page = 1;

        // Gestion de l'affichage des champs de date personnalisés
        if ($(this).val() === 'custom') {
            $('#customDateRange2').slideDown();
            // Ne pas filtrer immédiatement, attendre que les dates soient sélectionnées
        } else {
            $('#customDateRange2').slideUp();
            filterAndRender();
        }
    });
    $('#startDate2, #endDate2').on('change', function () {
        if ($('#filterPeriod2').val() === 'custom') {
            page = 1;
            filterAndRender();
        }
    });
    $('.cmdStat2').on('change', function () {
        page = 1;
        filterAndRender();
    });
    $('#orderPrevPage2').click(function () {
        if (page > 1) {
            page--;
            filterAndRender();
        }
    });
    $('#orderNextPage2').click(function () {
        page++;
        filterAndRender();
    });
    $('#printOrdersReport').click(function (e) {
        e.preventDefault();
        printOrdersReport();
    });
    $('#order2sStatsModal').on('shown.bs.modal', function () {
        // Réinitialisation des filtres
        $('#searchOrders2').val('');
        $('#filterPeriod2').val('all').trigger('change');
        $('.cmdStat2').prop('checked', false);

        // Réinitialisation de la pagination
        page = 1;

        // Chargement des données
        loadOrdersStatistics();
    });
    $('#printStatsButton2').on("click", function () {
        printOrdersReport();
    });
    stockProduts.tableBody.on('click', '.save-stock', handleSaveStock);
    stockProduts.searchInput.on('input', stockDebounce(stockHandleSearch, 300));
    $('#stockClearSearchBtn').click(stockClearSearch);
    stockProduts.statusFilter.change(stockHandleFilterChange);
    stockProduts.rowsPerPageSelect.change(stockHandleRowsPerPageChange);
    $(document).on('click', '.page-link', stockHandlePaginationClick);
    stockProduts.printBtn.click(stockPreparePrint);
    stockProduts.modal.on('shown.bs.modal', loadStockData);
    stockProduts.tableBody.on('input', '.stock-input', function () {
        validateNumericInput($(this));
    });
    $("#toast-confirm").on("click", function () {
        switch ($("#custom-toast").data("confirm")) {
            case "category":
                $.ajax({
                    url: `/ categories - delete `,
                    type: 'POST',
                    data: { categoryIdToDelete: $("#custom-toast").data("id") },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            $("#shcategoryDetailsModal").modal('hide');
                            $('#catModalTrig').trigger("click");
                            showToaster(response.message);
                        } else {
                            showToaster(response.message);
                        }
                    }, error: function () {
                        showToaster("Erreur lors de la suppression de la catégorie.");
                    }
                });
                break
            default:
                break;
        }
        $("#custom-toast").fadeOut();
    });

    let feedbacksData = []; // Pour stocker les données des messages
    let newOnly = 1;
    let feedbackCurrentPage = 1;
    const feedbackPerPage = 10;
    let currentFeedbackId = null;

    function loadFeedbacks(page) {
        // Récupérer les filtres
        const searchTerm = $("#searchFeedbacks").val();

        $.ajax({
            url: "/visitor/feedbacks/list",
            type: "POST",
            data: {
                page: page,
                per_page: feedbackPerPage,
                search: searchTerm,
                newOnly: newOnly
            },
            dataType: "json",
            success: function (response) {
                feedbacksData = response.data.messages; // Stocker les données

                renderFeedbacksTable(feedbacksData);

                // Mise à jour des boutons de pagination
                feedbackCurrentPage = response.data.current_page;
                $("#feedbackPaginationInfo").text(`Page ${feedbackCurrentPage} / ${response.data.total_pages}`);
                $("#feedbackPrevPage").prop("disabled", feedbackCurrentPage === 1);
                $("#feedbackNextPage").prop("disabled", feedbackCurrentPage === response.data.total_pages);
            },
            error: function () {
                showToaster("Erreur de chargement des messages.", "danger");
            }
        });
    }

    function renderFeedbacksTable(feedbacks) {
        $("#feedbacksTableBody").html("");
        if (feedbacks.length > 0) {
            feedbacks.forEach((feedback, index) => {
                let row = `
                <tr>
                    <td class="feedback-id">${feedback.id}</td>
                    <td class="feedback-email">${feedback.email}</td>
                    <td class="feedback-subject truncate">${feedback.subject}</td>
                    <td class="feedback-date">${feedback.sent_at}</td>
                    <td class="feedback-actions">
                        <button class="btn btn-info btn-sm btn-view-feedback" data-feedback-index="${index}">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete-feedback" data-feedback-id="${feedback.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
                $("#feedbacksTableBody").append(row);
            });
        } else {
            $("#feedbacksTableBody").html("<tr><td colspan='5'>Aucun message trouvé</td></tr>");
        }
    }


    // Chargement initial
    $("#feedbacksListModalTrigger").click(() => loadFeedbacks(feedbackCurrentPage));
    // Événements de pagination
    $("#feedbackPrevPage").click(() => {
        if (feedbackCurrentPage > 1) loadFeedbacks(feedbackCurrentPage - 1);
    });
    $("#feedbackNextPage").click(() => loadFeedbacks(feedbackCurrentPage + 1));

    // Recherche
    $("#searchFeedbackButton").click(() => loadFeedbacks(1)); // Retour à la page 1 pour la recherche
    $("#searchFeedbacks").on("keyup", function (e) {
        if (e.key === "Enter") {
            loadFeedbacks(1);
        }
    });

    // Tri par date
    $("#feedbacksSortBtn").click(function () {
        if (newOnly === 1) {
            $(this).html('<i class="fa fa-chevron-up"></i>');
            newOnly = 0;
        } else {
            $(this).html('<i class="fa fa-chevron-down"></i>');
            newOnly = 1;
        }
        $(this).data("sort-direction", newOnly);
        loadFeedbacks(1);
    });

    // Affichage des détails du message
    $(document).on("click", ".btn-view-feedback", function () {
        const feedbackIndex = $(this).data("feedback-index");
        const feedback = feedbacksData[feedbackIndex];

        currentFeedbackId = feedback.id;

        $("#feedbackName").text(feedback.name);
        $("#feedbackEmail").text(feedback.email);
        $("#feedbackSubject").text(feedback.subject);
        $("#feedbackMessage").text(feedback.message);
        $("#feedbackDate").text(feedback.date);

        // Réinitialiser le champ de réponse
        $("#replyMessage").val("");

        // Fermer le modal des feedbacks et ouvrir celui des détails
        $("#feedbacksModal").modal("hide");
        $("#feedbackDetailsModal").modal("show");
    });

    // Gestion de la suppression via le bouton de la liste
    $(document).on("click", ".btn-delete-feedback", function () {
        currentFeedbackId = $(this).data("feedback-id");
        $("#fecustom-toast").fadeIn();
        $("#fecustom-toast").data("confirm", "feedback");
        $("#fecustom-toast").data("id", currentFeedbackId);
    });

    // Gestion de la suppression via le bouton du détail
    $("#btnDeleteFeedback").click(function () {
        $("#fecustom-toast").fadeIn();
        $("#fecustom-toast").data("confirm", "feedback");
        $("#fecustom-toast").data("id", currentFeedbackId);
    });

    // Confirmation de suppression
    $("#toast-confirm").click(function () {
        if ($("#fecustom-toast").data("confirm") === "feedback") {
            $.ajax({
                url: "/visitor/feedbacks/delete",
                type: "POST",
                data: {
                    feedback_id: $("#fecustom-toast").data("id")
                },
                dataType: "json",
                success: function (response) {
                    $("#feedbackDetailsModal").modal("hide");
                    $("#fecustom-toast").fadeOut();
                    $("#feedbacksModal").modal("show");
                    showToaster("Message supprimé avec succès !", "success");
                    // Recharger les messages
                    loadFeedbacks(feedbackCurrentPage);
                },
                error: function () {
                    showToaster("Erreur lors de la suppression du message.", "danger");
                }
            });
        }
    });

    // Envoi de la réponse
    $("#btnSendReply").click(function () {
        const replyMessage = $("#replyMessage").val().trim();

        if (!replyMessage) {
            showToaster("Veuillez saisir un message de réponse.", "warning");
            return;
        }

        $.ajax({
            url: "/visitor/feedback",
            type: "POST",
            data: {
                email: $("#feedbackEmail").text(),
                name: $("#feedbackName").text(),
                message: replyMessage
            },
            dataType: "json",
            success: function (response) {

                if (response.success) {
                    showToaster(response.message);
                    $("#replyMessage").val("");
                } else {
                    showToaster(response.message);
                }
            },
            error: function () {
                showToaster("Erreur lors de l'envoi de la réponse.");
            }
        });
    });

});

