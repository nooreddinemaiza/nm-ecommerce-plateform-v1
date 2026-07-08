$(document).ready(function () {
    $(document).on('click', '.mainCTrig', function () {
        $("#mainContainerp").addClass("d-none");
    });
    setInterval(checkSessionStatus, (3.5) * 60 * 1000);
    $("#editProfileModalTrig").on("click", function () {
        $.ajax({
            type: "post",
            url: "manager/profile/get",
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    const data = response.data;
                    userDataStore = data;
                    $("#editUsername").val((data.username || '').trim());
                    $("#managerEmailSpaned").html((data.email || '').trim());
                    $("#editEmail").val((data.email || '').trim());
                    $("#editPhone").val((data.phone || '').trim());
                    $("#eManagerFullname").val((data.fullname || '').trim());
                }
            }
        });
    });
    $("#submitProfileMod").on("click", function (e) {
        e.preventDefault();

        const $submitBtn = $(this);

        // Champs actuels
        const username = $("#editUsername").val().trim();
        const email = $("#editEmail").val().trim();
        const phone = $("#editPhone").val().trim();
        const fullname = $("#eManagerFullname").val().trim();

        // Champs originaux (à placer en attribut data-original dans le HTML)
        const originalUsername = userDataStore.username;
        const originalEmail = userDataStore.email;
        const originalPhone = userDataStore.phone;
        const originalFullname = userDataStore.fullname;

        // Vérifier si les données ont changé
        if (
            username === originalUsername &&
            email === originalEmail &&
            phone === originalPhone &&
            fullname === originalFullname
        ) {
            showToaster("Aucune modification détectée.");
            return;
        }

        // Regex
        const phoneRegex = /^[0-9]{10,15}$/;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const usernameRegex = /^[a-zA-Z0-9_]{3,}$/;

        // Réinitialiser les erreurs
        $(".error-message").remove();

        let isValid = true;

        if (!username || !usernameRegex.test(username)) {
            $("#editUsername").after('<div class="error-message text-danger">Nom d\'utilisateur invalide (min. 3 caractères alphanumériques ou underscore).</div>');
            isValid = false;
        }

        if (!email || !emailRegex.test(email)) {
            $("#editEmail").after('<div class="error-message text-danger">Veuillez entrer un email valide.</div>');
            isValid = false;
        }

        if (phone && !phoneRegex.test(phone)) {
            $("#editPhone").after('<div class="error-message text-danger">Numéro de téléphone invalide (10 à 15 chiffres).</div>');
            isValid = false;
        }

        if (!isValid) return;

        // Bloquer le bouton
        $submitBtn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Envoi...');

        // Envoyer
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('fullname', fullname);
        formData.append('csrf_token', csrf_token);
        $.ajax({
            url: "/manager/profile/update",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                try {
                    const json = JSON.parse(response);

                    if (json.success) {
                        showToaster(json.message || 'Profil mis à jour avec succès.');
                        userDataStore = {};
                        $('#editProfileModal').modal('hide');
                    } else {
                        // Affiche les erreurs s’il y en a sous forme de tableau
                        if (Array.isArray(json.errors)) {
                            json.errors.forEach(err => showToaster(err));
                        } else if (json.message) {
                            showToaster(json.message);
                        } else {
                            showToaster('Une erreur inconnue est survenue');
                        }
                    }

                } catch (e) {
                    console.error('Erreur parsing JSON:', e, response);
                    showToaster('Réponse serveur invalide');
                }
            },
            error: function () {
                showToaster('Erreur réseau lors de la modification');
            },
            complete: function () {
                $submitBtn.prop("disabled", false).html('Modifier');
            }
        });

    });
    $('#newproductImages').on('change', function (event) {
        const files = Array.from(event.target.files);

        if (newImages.length + files.length > 4) {
            showToaster('Vous pouvez sélectionner au maximum 4 images.');
            return;
        }

        files.forEach(file => {
            // Vérifications
            if (file.size > 5 * 1024 * 1024) {
                showToaster(`L'image ${file.name} est trop volumineuse.`);
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                showToaster(`Le fichier ${file.name} n'est pas une image valide.`);
                return;
            }

            // Ajouter à la liste
            newImages.push(file);
        });

        // Affichage centralisé via la fonction stylisée
        showImagePreview(files, $('#imagePreview'), newImages, deletedImages);
    });
    $('#add-new-product').on('click', function (e) {
        e.preventDefault();
        const $button = $(this);
        const originalText = $button.html(); // Sauvegarde le texte initial du bouton
        $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Ajout en cours...'); // Ajoute un spinner

        // Validation du titre
        let title = $('#productName').val().trim();
        let slug = $('#productSlug').val().trim();
        if (!title) {
            showToaster('Le titre du produit ne peut pas être vide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        // Éliminer les espaces multiples
        title = title.replace(/\s+/g, ' ');

        if (!slug.match(/^[a-z0-9\-]+$/i)) {
            showToaster('Le slug doit contenir uniquement des lettres, chiffres ou tirets.');
            $button.prop('disabled', false).html(originalText);
        }

        // Validation des autres données
        const price = parseFloat($('#productPrice').val().trim()) || 0;
        if (!price || isNaN(price) || price < 0) {
            showToaster('Veuillez entrer un prix valide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        const stock = ($('#productStock').val().trim() !== "" && !isNaN(parseInt($('#productStock').val().trim())))
            ? parseInt($('#productStock').val().trim())
            : 0;
        if (isNaN(stock) || stock < 0) {
            showToaster('Veuillez entrer un stock valide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        const description = $('#productDescription').val().trim() || "";
        const meta_description = $('#metaDescription').val().trim() || "";
        const meta_tag = $('#metaProductTags').val().trim() || "";
        const tags = $('#productTags').val().trim() || "";
        const reduction = $('#productReduction').length ? $('#productReduction').val().trim() || 0 : 0;
        const appReduction = $('#productReductionThreshold').length ? $('#productReductionThreshold').val().trim() || 0 : 0;
        const categories = $('input[name="categories[]"]:checked').map(function () {
            return this.value;
        }).get();

        const productData = { title, price, stock, slug, description, tags, reduction, appReduction, categories, meta_description, meta_tag };

        const formData = new FormData();
        Object.entries(productData).forEach(([key, value]) => {
            formData.append(key, value);
        });

        if (newImages.length) {
            Array.from(newImages).forEach(file => {
                formData.append('images[]', file);
            });
        }

        $.ajax({
            url: '/add-product',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    showToaster('Produit ajouté avec succès !');
                    $('#addProductModal').modal('hide');
                    $('#productModal').modal('show');
                } else {
                    showToaster(response.errors);
                }
            },
            error: function (xhr, status, error) {
                showToaster('Erreur lors de la communication avec le serveur : ' + error);
            },
            complete: function () {
                $button.prop('disabled', false).html(originalText); // Restaure le bouton après la requête
            }
        });
    });
    $('#editProductImageInput').on('change', function (event) {
        const files = Array.from(event.target.files);

        const currentImageCount = $('#editProductImagesPreview .preview-container').length;
        if (files.length + currentImageCount > 4) {
            showToaster('Vous pouvez avoir au maximum 4 images.');
            return;
        }

        // Vérifier et filtrer les fichiers valides
        const validFiles = [];
        files.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                showToaster(`L'image ${file.name} est trop volumineuse.`);
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                showToaster(`Le fichier ${file.name} n'est pas une image valide.`);
                return;
            }
            editImages.push(file);
            validFiles.push(file);
        });

        // Affichage via la fonction centrale
        showImagePreview(validFiles, $('#editProductImagesPreview'), editImages, deletedImages);
    });
    $(document).on('click', '.editCategories', function () {
        const isChecked = $(this).prop('checked');
        const categoryId = parseInt($(this).val());
        // Si décoché et faisait partie des catégories initiales
        if (!isChecked && initialCategories.includes(categoryId)) {
            if (!deselectedCategories.includes(categoryId)) {
                deselectedCategories.push(categoryId);
            }
        }

        // Si coché et la catégorie est dans les désélections, on la retire
        if (isChecked && deselectedCategories.includes(categoryId)) {
            deselectedCategories = deselectedCategories.filter(id => id !== categoryId);
        }
    });

    $('#updateProduct').on('click', function (e) {
        e.preventDefault();

        const $button = $(this);
        const originalText = $button.html(); // Sauvegarde du texte initial
        $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mise à jour...'); // Ajoute un spinner

        // Validation du titre du produit
        let title = $('#editProductName').val().trim();
        let slug = $('#editProductSlug').val().trim();

        // Vérifications préliminaires avant l'envoi au serveur
        if (!title) {
            showToaster('Le titre du produit ne peut pas être vide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        if (!slug.match(/^[a-z0-9\-]+$/i)) {
            showToaster('Le slug doit contenir uniquement des lettres, chiffres ou tirets.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        // Éliminer les espaces multiples
        title = title.replace(/\s+/g, ' ');

        const selectedCategories = $('input[name="editCategories[]"]:checked').map(function () {
            return this.value;
        }).get();

        // Validation des autres données
        const id = parseInt($('#editProductName').data('id'));
        const price = parseFloat($('#editProductPrice').val().trim());
        const stock = parseInt($('#editProductStock').val().trim());
        const reduction = $('#editproductReduction').length ? parseFloat($('#editproductReduction').val().trim() || 0) : 0;
        const appReduction = $('#editproductReductionThreshold').length ? parseFloat($('#editproductReductionThreshold').val().trim() || 0) : 0;
        const description = $('#editProductDescription').val().trim();
        const tags = $('#editProductTags').val().trim();
        const meta_description = $('#editMetaProductDescription').val().trim();
        const meta_tags = $('#editMetaProductTags').val().trim();
        const status = $('input[name="editProductStatus"]:checked').val();

        // Vérification des données numériques
        if (!price || isNaN(price) || price < 0) {
            showToaster('Veuillez entrer un prix valide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        if (isNaN(stock) || stock < 0) {
            showToaster('Veuillez entrer un stock valide.');
            $button.prop('disabled', false).html(originalText);
            return;
        }

        const updatedProductData = {
            id: id,
            title: title,
            price: price,
            stock: stock,
            slug: slug,
            reduction: reduction,
            appReduction: appReduction,
            description: description,
            status: status,
            tags: tags,
            meta_description: meta_description,
            meta_tag: meta_tags,
            selectedCategories: selectedCategories.join(','),
            deselectedCategories: deselectedCategories.join(','),
        };

        const formData = new FormData();
        Object.entries(updatedProductData).forEach(([key, value]) => formData.append(key, value));

        // Ajout des images nouvelles et supprimées au FormData
        if (typeof editImages !== 'undefined' && editImages.length > 0) {
            editImages.forEach((file, index) => formData.append(`newImages[${index}]`, file));
        }

        if (typeof deletedImages !== 'undefined' && deletedImages.length > 0) {
            deletedImages.forEach(image => formData.append('deletedImages[]', image));
        }

        // Préparation de la zone de message pour afficher les détails des opérations
        const $statusContainer = $('#updateStatusContainer');
        if ($statusContainer.length === 0) {
            $('<div id="updateStatusContainer" class="mt-3 update-status-container"></div>').insertAfter($button);
        } else {
            $statusContainer.empty();
        }

        $.ajax({
            url: '/update-products',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                try {
                    // Assurer que la réponse est un objet JSON
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    // Réinitialiser le conteneur de statut
                    $statusContainer.empty();

                    // Gérer la réponse globale
                    if (response.success) {

                        // Afficher les détails des opérations
                        displayOperationStatus(response.operationStatus, $statusContainer);

                        // Réinitialiser l'état et rafraîchir l'interface
                        setTimeout(function () {
                            // Nettoyer les tableaux d'images
                            if (typeof imagesArray !== 'undefined') {
                                imagesArray = [];
                            }
                            if (typeof deletedImages !== 'undefined') {
                                deletedImages = [];
                            }

                            // Initialiser le gestionnaire de produits
                            if (typeof productManager !== 'undefined' && productManager.init) {
                                productManager.init();
                            }

                            // Fermer la modal d'édition et afficher la modal de produit
                            $('#editProductModal').modal('hide');

                            // Vérifier si la modal produit existe avant de l'afficher
                            if ($('#productModal').length) {
                                $('#productModal').modal('show');
                            }
                        }, 1500); // Délai pour permettre à l'utilisateur de voir les statuts
                    } else {

                        // Afficher les détails des opérations même en cas d'échec
                        displayOperationStatus(response.operationStatus, $statusContainer);
                    }
                } catch (e) {
                    showToaster('Erreur lors du traitement de la réponse : ' + e.message);
                    console.error('Erreur de parsing:', e);
                }
            },
            error: function (xhr, status, error) {
                // Gestion des erreurs HTTP
                let errorMessage = 'Erreur lors de la communication avec le serveur';

                try {
                    // Essayer de parser la réponse d'erreur si elle est en JSON
                    const responseJson = JSON.parse(xhr.responseText);
                    errorMessage = responseJson.message || `${errorMessage}: ${error}`;

                    // Afficher les détails des opérations s'ils existent
                    if (responseJson.operationStatus) {
                        displayOperationStatus(responseJson.operationStatus, $statusContainer);
                    }
                } catch (e) {
                    errorMessage = `${errorMessage}: ${error}`;
                }

                showToaster(errorMessage);

                // Réinitialiser les tableaux d'images
                if (typeof imagesArray !== 'undefined') {
                    imagesArray = [];
                }
                if (typeof deletedImages !== 'undefined') {
                    deletedImages = [];
                }
            },
            complete: function () {
                $button.prop('disabled', false).html(originalText); // Réactive le bouton après la requête
            }
        });
    });
    $('#productModal').on('change', '.prstatus-select', function () {
        let status = parseInt($(this).val());
        let id = $(this).data('id');
        let container = $(this).closest('.status-container');
        container.removeClass('bg-success bg-warning');
        if (status === 1) {
            container.addClass('bg-success');
        } else {
            container.addClass('bg-warning');
        }
        $.ajax({
            type: "post",
            url: "/products/status/update",
            data: {
                'id': id,
                'status': status
            },
            success: function (response) {
                response = JSON.parse(response);
                showToaster(response.message);
            }
        });
    });
    $(document).on('click', '.product-view-details', function () {
        imagesArray = [];
        images = [];
        const productId = $(this).data('id');
        $('#modify-product').data('id', productId);
        $('#delete-product-btn').data('id', productId);

        productManager.loadProduct(productId, function (product) {
            if ((product !== null) && (product !== undefined)) {
                // Titre de la modal et titre du produit
                $('#viewProductTitle').text(product.title);

                // Prix et anciennes valeurs
                $('#viewProductPrice').text(product.price ? product.price + ' DH' : 'N/A');
                $('#viewProductOldPrice').text(product.old_price ? product.old_price + ' DH' : '');

                // Affichage de la réduction sous forme de badge
                const reductionValue = product.reduction || '';
                if (reductionValue && reductionValue !== 'pas précisée') {
                    $('#viewProductReductionBadge').text('-' + reductionValue).show();
                } else {
                    $('#viewProductReductionBadge').hide();
                }

                // Lien du produit
                $('#viewProductLink').attr('href', product.link);

                // Statut
                if (product.status === 'reduit') {
                    $('#viewProductStatus').text('Réduit').removeClass().addClass('badge rounded-pill bg-warning text-dark');
                    $('#viewProductLink').addClass('disabled');
                } else {
                    $('#viewProductLink').removeClass('disabled');
                    $('#viewProductStatus').text('Visible').removeClass().addClass('badge rounded-pill bg-success');
                }

                // Informations principales
                $('#viewProductDescription').text(product.description || 'Aucune description disponible');
                $('#viewProductReduction').text(product.reduction || 'Non précisée');
                $('#viewProductAppReduction').text(product.appReduction || 'Non précisée');
                $('#viewProductCategory').text(Array.isArray(product.categories) ? product.categories.join(', ') : product.categories || 'Non catégorisé');
                $('#viewProductStock').text(product.stock || 'Non précisé');
                $('#viewProductSlug').text(product.slug || 'Non précisé');

                $('#delete-product-btn').data('id', product.id);
                // SEO et Tags
                $('#viewProductTag').text(product.tag || 'Non précisé');
                $('#viewProductMetaTag').text(product.meta_tag || 'Non précisé');
                $('#viewProductMetaDescription').text(product.meta_description || 'Non précisée');

                // Créateur
                $('#viewProductCreator').text(product.creator || 'Inconnu');

                // Visites
                $('#viewProductVisites').text(product.visites || '0');

                // Images du carrousel
                const $carouselInner = $('#productImagesPreview').empty();
                const productImages = Array.isArray(product.images) ? product.images : [product.images];

                if (productImages.length > 0 && productImages[0]) {
                    productImages.forEach((image, index) => {
                        let imageName = image.includes('|') ? image.split('|')[1] : image;
                        $carouselInner.append(`
                            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                <img src="/assets/images/product-image/${imageName}" class="d-block w-100" style="object-fit: contain; height: 400px;" alt="${product.title} - Image ${index + 1}">
                            </div>
                        `);
                    });
                } else {
                    // Image par défaut si aucune image disponible
                    $carouselInner.append(`
                        <div class="carousel-item active">
                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                <div class="text-center p-4">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune image disponible</p>
                                </div>
                            </div>
                        </div>
                    `);
                }

                $('#viewProductModal').modal('show');
            } else {
                showToaster('Erreur lors de la récupération des données du produit.');
            }
        });
    });
    $(document).on('click', '.modify-product', function () {
        initialCategories = [];
        deselectedCategories = [];
        imagesArray = [];
        editImages = [];
        deletedImages = [];
        images = [];
        const productId = $(this).data('id');
        productManager.loadProduct(productId, function (product) {
            if ((product !== null) && (product !== undefined)) {

                if (Array.isArray(product.images)) {
                    imagesArray.push(...product.images);
                } else if (product.images) {
                    imagesArray.push(product.images);
                }

                if (Array.isArray(product.categorieID)) {
                    initialCategories.push(...product.categorieID);
                } else if (product.categorieID) {
                    initialCategories.push(product.categorieID);
                }

                $('#editProductName').data('id', product.id);
                $("#editProductModal").modal("show");
                $('#viewProductModal').modal('hide');
                $('#updateProduct').prop('disabled', false).html('Mettre à jour');

                const currentImages = product.images;
                const $container = $('#editProductImagesPreview').empty();
                if (currentImages) {
                    let imagesToDisplay = [];
                    if (typeof currentImages === 'string') {
                        imagesToDisplay.push(createFakeFileObject(currentImages));
                    } else if (Array.isArray(currentImages) && currentImages.length) {
                        currentImages.forEach(image => imagesToDisplay.push(createFakeFileObject(image)));
                    }
                    showImagePreview(imagesToDisplay, $container, imagesArray, deletedImages);
                }
                $('#editProductName').val(product.title);
                $('#editProductSlug').val(product.slug);
                $('#editProductPrice').val(product.price);
                $('#editProductStock').val(product.stock);
                $('#editProductDescription').val(product.description);
                $('#editMetaProductDescription').val(product.meta_description);
                $('#editproductReduction').val(product.reduction || 0);
                $('#editproductReductionThreshold').val(product.appReduction || 0);
                $('#editProductTags').val(product.tag);
                $('#editMetaProductTags').val(product.meta_tag);
                product.status === 'affiche' ? $('.affiche').prop('checked', true) : $('.reduit').prop('checked', true);
                let categories = product.categories;
                if (!Array.isArray(categories)) {
                    categories = categories ? [categories] : [];
                }
                $('#editProductCategories .form-check input[type="checkbox"]').prop('checked', false);
                categories.forEach(categoryTitle => {
                    $('#editProductCategories .form-check').each(function () {
                        let edCatLab = $(this).find('label').attr('title').trim();
                        if (edCatLab === categoryTitle) {
                            $(this).find('input[type="checkbox"]').prop('checked', true);
                        }
                    });
                });

            } else {
                showToaster('Erreur lors de la récupération des données du produit.');
            }
        });
    });
    $(document).on('click', '.delte-product2', function () {
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "product");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", $(this).data('id'));

    });
    $('#productModal').on('shown.bs.modal', function () {
        productManager.init();
    });
    // Utilisation de la délégation d'événements pour les éléments .stock-input
    $(document).on('keyup', '#observeStockModal .stock-input', function () {
        $(this).closest('tr').find('.save-stock').prop('disabled', false);
    });
    $(document).on('change', '#observeStockModal .stock-input', function () {
        $(this).closest('tr').find('.save-stock').prop('disabled', false);
    });
    // Bouton pour uploader les images dans le modal d'édition
    $('#uploadImagesBtn').on('click', function () {
        $('#editProductImageInput').click();
    });
    // Bouton pour effacer les images dans le modal d'ajout
    $('#clearImagesBtn').on('click', function () {
        $('#imagePreview').empty();
        $('#newproductImages').val('');
        newImages = [];
    });

    // Pour le modal d'ajout
    const editTabs = document.querySelectorAll('#productEditTabs .nav-link');
    const addTabs = document.querySelectorAll('#productAddTabs .nav-link');
    addTabs.forEach(tab => {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            const tabId = this.getAttribute('data-bs-target');

            // Désactiver tous les onglets
            addTabs.forEach(t => {
                t.classList.remove('active');
                const target = document.querySelector(t.getAttribute('data-bs-target'));
                target.classList.remove('show', 'active');
            });

            // Activer l'onglet cliqué
            this.classList.add('active');
            const target = document.querySelector(tabId);
            target.classList.add('show', 'active');
        });
    });
    // Pour le modal d'édition
    editTabs.forEach(tab => {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            const tabId = this.getAttribute('data-bs-target');

            // Désactiver tous les onglets
            editTabs.forEach(t => {
                t.classList.remove('active');
                const target = document.querySelector(t.getAttribute('data-bs-target'));
                target.classList.remove('show', 'active');
            });

            // Activer l'onglet cliqué
            this.classList.add('active');
            const target = document.querySelector(tabId);
            target.classList.add('show', 'active');
        });
    });

    $('.modal').on('hidden.bs.modal', function () {
        $('#addProductModal .preview-container').each(function () {
            $(this).remove();
        });
        $(this).find('input, select, textarea').each(function () {
            if ($(this).is(':checkbox') || $(this).is(':radio')) {
                $(this).prop('checked', false);

            } else {
                $(this).val('');

            }
        });
        $(this).find('select').prop('selectedIndex', 0);
    });
    $('.modal').each(function () {
        $(this).modal({ backdrop: 'static', keyboard: false });
    });
    $("#sendRPLink").on("click", function () {
        $(this).attr("disabled", true);
        const email = $('#managerEmailSpaned').text().trim();
        // Vérification côté serveur avec AJAX
        $.ajax({
            url: '/password-reset',
            type: 'POST',
            data: { email: email, csrf_token: csrf_token },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    showToaster(data.message);
                    $("#passwordResetModal").modal("hide");
                    $(this).attr("disabled", false);
                } else {
                    showToaster(data.message);
                }
            },
            error: function () {
                $(this).attr("disabled", false);
                showToaster('Erreur lors de l’envoi, veuillez réessayer.');
            },
            complete: function () {
                $("#passwordResetModal").modal("hide");
            }
        });
    });
    $('.articles-manage').on('click', function (e) {
        e.preventDefault();
        $('#list-article-tab').trigger("click");
    });

    // Load articles when the tab is clicked
    $('#list-article-tab').on('click', function (e) {
        e.preventDefault();
        ArticleManager.loadArticles();
    });

    // Form submit handler
    $('#newArticleForm').on('submit', function (e) {
        e.preventDefault();
        if (ArticleManager.validateArticleForm('add')) {
            ArticleManager.addArticle(this);
        }
    });
    ArticleManager.initTinyMCE("#content");
    // Title to slug auto-generation
    $('.man-title').on('input', function () {
        let slugInput = $(this).closest('.row').find('.man-slug input');

        if (ArticleManager.slugAutoGenerateEnabled) {
            slugInput.val(ArticleManager.generateSlugFromTitle($(this).val()));
        }
    });
    // Disable auto slug generation when manually editing the slug
    $('.man-slug-input').on('input', function () {
        ArticleManager.slugAutoGenerateEnabled = false;
    });

    $('#addProductBtnMobile').on('click', function () {
        $('#productSlug').val('');
        ArticleManager.slugAutoGenerateEnabled = true;
    });
    // Word count for excerpt
    $('#excerpt').on('input', function () {
        let text = $(this).val();
        let words = text.trim().split(/\s+/);
        let remaining = EXT_MWC - words.length;
        $('#extwc').text(remaining >= 0 ? remaining : 0);

        if (words.length > EXT_MWC) {
            $('#extwc').addClass("text-danger");
            // Prevent adding more words
            $(this).val(words.slice(0, EXT_MWC).join(' '));
        } else {
            $('#extwc').removeClass("text-danger");
        }
    });

    // Search input event handler (with debounce)
    $("#article-search").on('input', function () {
        clearTimeout(ArticleManager.searchTimeout);
        ArticleManager.searchTimeout = setTimeout(ArticleManager.loadArticles, 300);
    });

    // Filter and sort change handlers
    $("#article-filter, #article-sort").on('change', ArticleManager.loadArticles);

    // Refresh button handler
    $("#refresh-articles").on('click', ArticleManager.loadArticles);

    // Article edit and delete handlers (delegated events for dynamically created buttons)
    $(document).on('click', '.articleEdit', function () {
        const articleId = $(this).closest('tr').data('id');
        ArticleManager.loadArticleForEditing(articleId);
    });

    $(document).on('click', '.articleDelete', function () {
        const articleId = $(this).closest('li').data('id');
        const articleTitle = $(this).closest('li').find('.truncate b').text();
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "article");
        $("#custom-toast").data("index", "delete");
        $("#custom-toast").data("id", articleId);
    });
    // Title to slug auto-generation
    $('#edit_title').on('input', function () {
        if (ArticleManager.editSlugAutoGenerateEnabled) {
            $('#edit_slug').val(ArticleManager.generateSlugFromTitle($(this).val()));
        }
    });

    // Disable auto slug generation when manually editing the slug
    $('#edit_slug').on('input', function () {
        ArticleManager.editSlugAutoGenerateEnabled = false;
    });

    // Word count for excerpt
    $('#edit_excerpt').on('input', function () {
        let text = $(this).val();
        let words = text.trim().split(/\s+/);
        let remaining = EXT_MWC - words.length;
        $('#edit_extwc').text(remaining >= 0 ? remaining : 0);

        if (words.length > EXT_MWC) {
            $('#edit_extwc').addClass("text-danger");
            // Prevent adding more words
            $(this).val(words.slice(0, EXT_MWC).join(' '));
        } else {
            $('#edit_extwc').removeClass("text-danger");
        }
    });

    // Image handling
    $('#keep_current_image').on('change', function () {
        if ($(this).is(':checked')) {
            $('#edit_image').prop('disabled', true).addClass('opacity-50');
        } else {
            $('#edit_image').prop('disabled', false).removeClass('opacity-50');
        }
    });

    // Modal opening handler
    $('#editArticleModal').on('show.bs.modal', function () {
        $('#editArticleForm').hide();
        $('#editArticleLoading').show();

        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.text-danger').remove();

        // Reset slug auto-generation flag
        ArticleManager.editSlugAutoGenerateEnabled = true;
    });

    // Save button handler
    $('#saveArticleChanges').on('click', function () {
        if (ArticleManager.validateArticleForm('edit')) {
            ArticleManager.updateArticle();
        }
    });
    $('.meta_tags_input').on('keydown', function (e) {
        let key = e.key;
        let separators = ['Enter', ',']; // On retire l'espace ici

        if (separators.includes(key)) {
            e.preventDefault();

            let currentValue = $(this).val().trim();

            if (currentValue !== '' && !currentValue.endsWith(',')) {
                $(this).val(currentValue + ', ');
            }
        }
    });

    $("#toast-confirm").on("click", function () {
        switch ($("#custom-toast").data("confirm")) {
            case 'product':
                const id = $("#custom-toast").data("id");
                const $statusContainer = $('#updateStatusContainer');
                if ($statusContainer.length === 0) {
                    $('body').append($('<div id="updateStatusContainer" class="mt-3 update-status-container"></div>'));
                } else {
                    $statusContainer.empty();
                }
                $.ajax({
                    url: '/delete-product',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#confirmDeleteProductBtn').prop('disabled', true).text('Suppression...');
                    }, success: function (response) {
                        productManager.loadProducts();
                        $statusContainer.empty();
                        if (response.operationStatus && response.operationStatus.length > 0) {
                            displayOperationStatus(response.operationStatus, $statusContainer);
                        } else {
                            displayOperationStatus(response.operationStatus, $statusContainer);
                        }
                    }, error: function (xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Erreur lors de la communication avec le serveur.';
                        showToaster(errorMessage);

                    }, complete: function () {
                        $('#confirmDeleteProductBtn').prop('disabled', false).text('Confirmer');
                        $('#productModal').modal('show');
                    }
                });
                break;
            default:
                break;
        }
        $("#custom-toast").fadeOut();
    });
});

