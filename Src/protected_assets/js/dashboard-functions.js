let imagesArray = [];
let deletedImages = [];
let userDataStore = {};
let productData = {};
let images = [];
let page = null;
let imids = [];
let editImages = [];
let newImages = [];
const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
const maxImages = 4;
let category = {};
let selectedIds = [];
const maxSelection = 4;
var selectedProduct = null;
let selectedStats = [];
let initialCategories = [];
let deselectedCategories = [];
let selectedCategories = [];
let trendCategories = { removed: [], added: [] };
let selectedEmails = [];
let ordersCurrentPage = 1;
const ordersPerPage = 5;
let currentOrderId = null;
let ordersData = []; // Pour stocker les données des commandes
let ordersNewOnly = 1;
const STOCK_THRESHOLD = 5;
//sections
let currentSectionId = null;
let sectionData = {};
let currentModelId = null;
let currentPage = "home";
let addOrEdit = null;
let sortableList = null;


const EXT_MWC = 200;
let editSlugAuto = false;
let editTinyMCE = null;
let slugAuto = true;
////////
const API = {
    UPDATE_STOCK: '/products/stock/update'
};
const stockState = {
    allStockData: [],
    currentPage: 1,
    rowsPerPage: ordersPerPage,
    currentFilter: 'all',
    currentSearch: '',
    isLoading: false
};

const productManager = {
    config: {
        currentPage: 1,
        perPage: 5,
        totalItems: 0,
        debounceTimeout: null,
        debounceDelay: 300
    },

    // Sélecteurs DOM
    elements: {
        searchInput: document.getElementById('searchInput'),
        sortBy: document.getElementById('sortBy'),
        orderBy: document.getElementById('orderBy'),
        tableBody: document.getElementById('productTableBody'),
        pagination: document.getElementById('pagination'),
        loadingIndicator: document.getElementById('loadingIndicator'),
        errorMessage: document.getElementById('errorMessage'),
        totalItems: document.getElementById('totalItems'),
        productModal: document.getElementById('productModal')
    },

    // Initialisation
    init: function () {
        this.bindEvents();
        this.loadProducts();
        this.loadCategories();
        // Vérifier si la modal est déjà initialisée par Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const productModalEl = this.elements.productModal;
            productModalEl.addEventListener('shown.bs.modal', () => {
                this.elements.searchInput.focus();
            });
        }
    },
    loadCategories: function () {
        $.ajax({
            type: "post",
            url: "/dashboard/categories-mod",
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    $('#addProductModalCatego').html("");
                    $('#editProductCategories').html("");
                    response.data.forEach(category => {
                        let title = category.title;
                        let id = category.id;
                        $('#addProductModalCatego').append(`
                            <div class="form-check btn-group-sm">
                                <input class="form-check-input" type="checkbox" id="add-pr-cat_${id}" name="categories[]" value="${id}">
                                <label class="form-check-label" for="add-pr-cat_${id}" title="${title}" style="cursor: pointer;">${title}</label>
                            </div>`);
                        $('#editProductCategories').append(`
                            <div class="form-check btn-group-sm">
                                <input class="form-check-input editCategories" type="checkbox" id="editCategory_${id}" name="editCategories[]" value="${id}">
                                <label class="form-check-label" for="editCategory_${id}" title="${title}" style="cursor: pointer;">${title}</label>
                            </div>`);
                    })
                }
            }
        });
    },
    // Enregistrement des événements
    bindEvents: function () {
        // Recherche
        this.elements.searchInput.addEventListener('input', this.debounce(() => {
            this.config.currentPage = 1;
            this.loadProducts();
        }, 300));

        // Tri
        this.elements.sortBy.addEventListener('change', () => {
            this.config.currentPage = 1;
            this.loadProducts();
        });

        // Ordre
        this.elements.orderBy.addEventListener('change', () => {
            this.config.currentPage = 1;
            this.loadProducts();
        });

        // Pagination
        this.elements.pagination.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.tagName === 'A' || e.target.parentElement.tagName === 'A') {
                const pageLink = e.target.closest('a');
                if (pageLink && pageLink.dataset.page) {
                    const page = parseInt(pageLink.dataset.page);
                    if (page !== this.config.currentPage) {
                        this.config.currentPage = page;
                        this.loadProducts(page);
                    }
                }
            }
        });
    },

    // Fonction de chargement des produits modifiée
    loadProducts: function (page = this.config.currentPage) {
        // Afficher l'indicateur de chargement
        this.toggleLoading(true);

        // Masquer les messages d'erreur
        this.showError('');

        // Récupérer les paramètres de recherche et de tri
        const search = this.elements.searchInput.value.trim();
        const sortBy = this.elements.sortBy.value;
        const order = this.elements.orderBy.value;

        // Appel AJAX
        fetch('/dashboard/products/paginate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                page: page,
                per_page: this.config.perPage,
                sortBy: sortBy,
                order: order,
                search: search
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.updateTable(data.data.products, data.is);
                    this.updatePagination(data.data.pagination);
                    this.config.totalItems = data.data.pagination.total_items || 0;
                    this.elements.totalItems.textContent = this.config.totalItems;
                } else {
                    this.showError(data.message || 'Aucun produit trouvé.');
                    this.updateTable([]);
                    this.updatePagination({
                        current_page: 1,
                        total_pages: 0
                    });
                }
            })
            .catch(error => {
                this.showError('Erreur de chargement des produits: ' + error.message);
                console.error('Erreur:', error);
            })
            .finally(() => {
                this.toggleLoading(false);
            });
    },
    //chargement d'un produit
    loadProduct: function (id, callback) {
        $.ajax({
            url: '/dashboard/view-product',
            type: 'post',
            data: { id: id },
            success: function (response) {
                const productData = JSON.parse(response);
                if (!productData.length) return callback(null);
                callback(productData[0]);
            },
            error: function () {
                callback(null);
            }
        });
    },
    // Fonction pour afficher ou masquer l'indicateur de chargement
    toggleLoading: function (show) {
        if (show) {
            this.elements.loadingIndicator.classList.remove('d-none');
        } else {
            this.elements.loadingIndicator.classList.add('d-none');
        }
    },

    // Mise à jour du tableau des produits - adaptée pour n'afficher que les infos de base
    updateTable: function (products, is) {
        this.elements.tableBody.innerHTML = '';
        if (!products || products.length === 0) {
            this.elements.tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-3">
                    <div class="text-muted">
                        <i class="fas fa-box-open me-2"></i>
                        Aucun produit trouvé.
                    </div>
                </td>
            </tr>
        `;
            return;
        }

        let html = '';
        products.forEach(product => {
            // Échapper les valeurs HTML pour éviter les injections XSS
            const id = product.id;
            const title = this.escapeHtml(product.title);
            const price = parseFloat(product.price).toFixed(2);
            const stock = parseInt(product.stock);
            const status = product.status;
            const statusClass = product.status === 'affiche' ? 'bg-success' : 'bg-warning';
            const visited = product.visited || 0;

            html += `
        <tr>
            <td>${title}</td>
            <td>${price} DH</td>
            <td class="text-success">${visited}</td>
            <td>${stock}</td>
            <td>
                <div class="btn-group btn-group-sm status-container ${statusClass}">
                    ${is
                    ? `<span>${product.status === 'affiche' ? 'Visible' : 'Réduit'}</span>`
                    : `
                        <select class="form-select prstatus-select" data-id="${id}">
                            <option value="1" ${status === 'affiche' ? 'selected' : ''}>Visible</option>
                            <option value="0" ${status === 'reduit' ? 'selected' : ''}>Réduit</option>
                        </select>
                      `
                }
                </div>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${is
                    ? ''
                    : `<button class="btn btn-outline-warning btn-sm modify-product" title="Modifier" data-id="${product.id}">
                            <i class="fas fa-edit"></i>
                    </button>`
                }
                    <button class="btn btn-outline-info btn-sm product-view-details" data-id="${product.id}">
                        <i class="fas fa-eye me-1"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm delte-product2" data-id="${product.id}">
                        <i class="fas fa-trash me-1"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
        });
        this.elements.tableBody.innerHTML = html;
    },

    // Mise à jour de la pagination - optimisée et simplifiée
    updatePagination: function (pagination) {
        const totalPages = pagination.total_pages || 0;
        const currentPage = pagination.current_page || 1;

        if (totalPages <= 1) {
            this.elements.pagination.innerHTML = '';
            return;
        }

        let html = '';

        // Bouton précédent
        html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Précédent">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    `;

        // Afficher un nombre limité de pages avec ellipse
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        // Ajuster si on est près du début ou de la fin
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        // Première page si nécessaire
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Pages numérotées
        for (let i = startPage; i <= endPage; i++) {
            html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
        }

        // Dernière page si nécessaire
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Bouton suivant
        html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Suivant">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    `;

        this.elements.pagination.innerHTML = html;
    },
    // Affichage des messages d'erreur
    showError: function (message) {
        if (message) {
            this.elements.errorMessage.textContent = message;
            this.elements.errorMessage.classList.remove('d-none');
        } else {
            this.elements.errorMessage.classList.add('d-none');
        }
    },

    // Affichage des notifications toast
    showToaster: function (message, type = 'error') {
        showToaster(message);
    },

    // Échappement HTML pour prévenir les injections XSS
    escapeHtml: function (unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    },
    debounce: function (func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

const ArticleManager = {
    slugAutoGenerateEnabled: true,
    editSlugAutoGenerateEnabled: true,
    searchTimeout: null,
    // Private utility functions
    generateSlugFromTitle: function (title) {
        return title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // remove special characters
            .replace(/\s+/g, '-')         // replace spaces with hyphens
            .replace(/-+/g, '-');         // avoid double hyphens
    },

    validateArticleForm: function (formType) {
        const isAdd = formType === 'add';
        const prefix = isAdd ? '' : 'edit_';
        let isValid = true;
        let errors = [];

        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.text-danger').remove();

        // Title validation
        let title = $(`#${prefix}title`).val().trim();
        if (!title) {
            isValid = false;
            errors.push({
                field: `#${prefix}title`,
                message: 'Le titre est requis.'
            });
        }

        // Slug validation
        let slug = $(`#${prefix}slug`).val().trim();
        if (!slug.match(/^[a-z0-9\-]+$/i)) {
            isValid = false;
            errors.push({
                field: `#${prefix}slug`,
                message: 'Le slug doit contenir uniquement des lettres, chiffres ou tirets.'
            });
        }

        // Content validation
        tinymce.triggerSave();
        let contentField = isAdd ? 'content' : 'edit_content';
        let content = tinymce.get(contentField).getContent({
            format: 'text'
        }).trim();
        if (!content) {
            isValid = false;
            errors.push({
                field: `#${contentField}`,
                message: 'Le contenu ne peut pas être vide.'
            });
        }

        // Excerpt validation
        let excerpt = $(`#${prefix}excerpt`).val().trim();
        let wordCount = excerpt.split(/\s+/).filter(w => w.length > 0).length;
        if (wordCount > EXT_MWC) {
            isValid = false;
            errors.push({
                field: `#${prefix}excerpt`,
                message: `L'extrait ne peut pas dépasser ${EXT_MWC} mots.`
            });
        }

        // Image validation
        if (isAdd || !$('#keep_current_image').is(':checked')) {
            let imageInput = $(`#${prefix}image`)[0];
            if (imageInput.files.length > 0) {
                let file = imageInput.files[0];
                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    errors.push({
                        field: `#${prefix}image`,
                        message: 'Seules les images JPG, PNG ou WEBP sont autorisées.'
                    });
                }
            }
        }

        // Display errors if needed
        if (!isValid) {
            errors.forEach(err => {
                let $field = $(err.field);
                $field.addClass('is-invalid');
                $field.after(`<div class="text-danger">${err.message}</div>`);
            });
        }

        return isValid;
    },
    // Article List Functions
    loadArticles: function () {
        const articlesTableBody = $("#articles-table-body");
        const searchTerm = $("#article-search").val().toLowerCase();
        const filterValue = $("#article-filter").val();
        const sortValue = $("#article-sort").val();

        // Show loading indicator
        articlesTableBody.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Chargement des articles...</td></tr>');

        $.ajax({
            type: "post",
            url: "manager/articles/list",
            success: function (response) {
                articlesTableBody.empty();
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (response.success && response.data.length > 0) {
                    // Store articles in memory for filtering/sorting
                    let articles = response.data;

                    // Apply filters
                    if (filterValue === "published") {
                        articles = articles.filter(article => article.is_published);
                    } else if (filterValue === "unpublished") {
                        articles = articles.filter(article => !article.is_published);
                    }

                    // Apply search term
                    if (searchTerm) {
                        articles = articles.filter(article =>
                            article.id.toString().includes(searchTerm) ||
                            article.title.toLowerCase().includes(searchTerm) ||
                            article.creator.toLowerCase().includes(searchTerm)
                        );
                    }

                    // Apply sorting
                    articles.sort((a, b) => {
                        switch (sortValue) {
                            case "id-asc":
                                return a.id - b.id;
                            case "id-desc":
                                return b.id - a.id;
                            case "title-asc":
                                return a.title.localeCompare(b.title);
                            case "title-desc":
                                return b.title.localeCompare(a.title);
                            default:
                                return a.id - b.id;
                        }
                    });

                    // Update article count
                    $("#article-count").text(`${articles.length} article${articles.length !== 1 ? 's' : ''} trouvé${articles.length !== 1 ? 's' : ''}`);

                    // Render articles
                    if (articles.length === 0) {
                        articlesTableBody.html('<tr><td colspan="6" class="text-center text-muted">Aucun article trouvé</td></tr>');
                    } else {
                        articles.forEach(function (article) {
                            let id = article.id;
                            let title = article.title;
                            let creator = article.creator;
                            let visites = article.visites;
                            let publishedBadge = article.is_published ?
                                '<span class="badge bg-success">Publié</span>' :
                                '<span class="badge bg-warning">Non publié</span>';

                            const articleRow = `
                                <tr data-id="${id}">
                                    <td>${id}</td>
                                    <td class="text-truncate" style="max-width: 200px;">${title}</td>
                                    <td>${visites}</td>
                                    <td>${creator}</td>
                                    <td>${publishedBadge}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary articleEdit" data-bs-toggle="modal" data-bs-target="#editArticleModal" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger articleDelete" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            articlesTableBody.append(articleRow);
                        });
                    }
                } else {
                    articlesTableBody.html('<tr><td colspan="6" class="text-center text-muted">Aucun article disponible</td></tr>');
                    $("#article-count").text("0 article trouvé");
                }
            },
            error: function () {
                articlesTableBody.html('<tr><td colspan="6" class="text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors du chargement des articles</td></tr>');
                $("#article-count").text("0 article trouvé");
            }
        });
    },
    // TinyMCE Functions
    initTinyMCE: function (selector) {
        return tinymce.init({
            language: 'fr_FR',
            selector: selector,
            plugins: [
                'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image',
                'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            ],
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
            setup: function (editor) {
                editor.on('init', function () {
                    setTimeout(function () {
                        $(".tox-promotion").remove();
                    }, 500);
                });
            }
        });
    },
    initEditTinyMCE: function () {
        editTinyMCEInstance = this.initTinyMCE('#edit_content');
    },
    // CRUD Operations
    loadArticleForEditing: function (articleId) {
        var $submitButton = $('#saveArticleChanges');
        $submitButton.prop('disabled', false).text('Enregistrer les modifications');

        // Initialize TinyMCE
        this.initEditTinyMCE();
        // Load article data
        $.ajax({
            type: 'post',
            url: '/manager/articles/getSingle/',
            data: { id: articleId },
            success: function (response) {
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (response.success) {
                    const article = response.data;
                    userDataStore = article;
                    // Fill the form with article data
                    $('#edit_article_id').val(article.id);
                    $('#edit_title').val(article.title);
                    $('#edit_slug').val(article.slug);
                    $('#edit_slug').val(article.slug);
                    const meta = article.meta ? JSON.parse(article.meta) : {};
                    $('#earticle_meta_descp').val(meta.description || "");
                    $('#earticle_meta_tags').val(meta.tags || "");

                    // Handle image
                    if (article.image) {
                        $('#current_image_preview').attr('src', article.image_url).show();
                        $('#current_image_name').text(article.image);
                        $('#keep_current_image').prop('checked', true).trigger('change');
                    } else {
                        $('#current_image_preview').hide();
                        $('#current_image_name').text('Aucune image');
                        $('#keep_current_image').prop('checked', false).trigger('change');
                    }

                    // Wait for TinyMCE to initialize before setting content
                    setTimeout(function () {
                        if (tinymce.get('edit_content')) {
                            tinymce.get('edit_content').setContent(article.content || '');
                        }
                    }, 300);

                    $('#edit_excerpt').val(article.excerpt || '').trigger('input');
                    $('#edit_afficher').prop('checked', article.is_published);

                    // Show the form
                    $('#editArticleLoading').hide();
                    $('#editArticleForm').show();
                } else {
                    // Handle error
                    $('#editArticleLoading').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Erreur lors du chargement de l'article: ${response.message || 'Erreur inconnue'}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                // Handle error
                $('#editArticleLoading').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement de l'article: ${error || 'Erreur inconnue'}
                    </div>
                `);
            }
        });
    },
    addArticle: function (form) {
        var formData = new FormData(form);
        var $submitButton = $(form).find('button[type="submit"]');

        $submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi...');

        $.ajax({
            type: 'POST',
            url: '/manager/articles/add',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (!response.success) {
                    showToaster("Veuillez vérifier les champs !");
                    if (response.errors) {
                        for (const [field, message] of Object.entries(response.errors)) {
                            const $field = $(`#${field}`);
                            $field.addClass('is-invalid').after(`<div class="text-danger">${message}</div>`);
                        }
                    }
                    $submitButton.prop('disabled', false).text('Ajouter');
                    return;
                }

                showToaster(response.message || 'Article ajouté avec succès');
                form.reset();
                tinymce.get('content').setContent('');
                $('#extwc').text(EXT_MWC);
                slugAutoGenerateEnabled = true;
                $submitButton.prop('disabled', false).text('Ajouter');

                if ($('#list-article-tab').hasClass('active')) {
                    loadArticles();
                }
            },
            error: function () {
                showToaster('Erreur lors de l\'ajout de l\'article');
                $submitButton.prop('disabled', false).text('Ajouter');
            }
        });
    },
    updateArticle: function () {
        const managerInstance = this;
        const form = document.getElementById('editArticleForm');
        const formData = new FormData(form);
        const $submitButton = $('#saveArticleChanges');

        const originalData = userDataStore; // données initiales stockées lors de l'ouverture du formulaire
        const currentData = {};

        // Champs à surveiller (ajuster selon tes champs HTML)
        const fieldsToCheck = ['title', 'slug', 'excerpt', 'content', 'status'];

        let hasChanges = false;

        fieldsToCheck.forEach(field => {
            let newValue;
            if (field === 'content' && typeof tinymce !== 'undefined') {
                newValue = tinymce.get('edit_content').getContent().trim();
                formData.set('content', newValue); // mettre à jour aussi la FormData
            } else {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    newValue = input.value.trim();
                }
            }

            const originalValue = originalData[field]?.trim() ?? '';
            if (newValue !== originalValue) {
                hasChanges = true;
                currentData[field] = newValue;
            }
        });

        if (!hasChanges) {
            showToaster("Aucune modification détectée.");
            return;
        }

        $submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enregistrement...');

        $.ajax({
            type: 'POST',
            url: '/manager/articles/update',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (!response.success) {
                    showToaster(response.message);
                    if (response.errors) {
                        for (const [field, message] of Object.entries(response.errors)) {
                            const $field = $(`#edit_${field}`);
                            $field.addClass('is-invalid').after(`<div class="text-danger">${message}</div>`);
                        }
                    }
                    $submitButton.prop('disabled', false).text('Enregistrer les modifications');
                    return;
                }
                userDataStore = {};
                showToaster(response.message || 'Article mis à jour avec succès');
                $('#editArticleModal').modal('hide');
                managerInstance.loadArticles();
                $submitButton.prop('disabled', false).text('Enregistrer les modifications');
            },
            error: function () {
                showToaster('Erreur lors de la mise à jour de l\'article');
                $submitButton.prop('disabled', false).text('Enregistrer les modifications');
            }
        });
    }

};
function debounce(func, wait) {
    return function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(func, wait);

    };
}
function updateImagesPreview() {
    const $imagesPreview = $('#editProductImagesPreview');
    $imagesPreview.empty();
    if (imagesArray.length > 0) {
        imagesArray.forEach((image, index) => {
            const imageUrl = typeof image === 'string' ? `/assets/images/product-image/${image}` : URL.createObjectURL(image);
            $imagesPreview.append(`<div class="position-relative" style="width: 200px;
     height: 200px;
    "><img src="${imageUrl}" class="img-thumbnail" alt="Image ${index + 1}" style="width: 200px;
     height: 200px;
     object-fit: cover;
    "><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="removeImage(${index})"><i class="fa-solid fa-trash"></i></button></div>`);
        });
    } else {
        $imagesPreview.append('<p class="text-muted">Aucune image disponible</p>');

    }
}
/**
 * Affiche un aperçu stylisé des images (nouvelles ou existantes).
 * @param {Array} files - Tableau de fichiers ou chemins d'images.
 * @param {jQuery} container - Conteneur jQuery où afficher les images.
 * @param {Array} imagesArray - Tableau pour stocker les fichiers/IDs.
 * @param {Array} deletedImages - Tableau pour stocker les images supprimées.
 */
function showImagePreview(files, container, imagesArray = [], deletedImages = []) {
    if (!Array.isArray(files) || files.length === 0) {
        showToaster("Aucune image à afficher.");
        return;
    }

    files.forEach(file => {
        let imageId = null;
        let imageUrl = file.url || file;
        let isExisting = typeof file === 'object' && file.url;

        // Traitement des images existantes encodées "id|filename"
        if (typeof imageUrl === 'string' && imageUrl.includes('|')) {
            const parts = imageUrl.split('|');
            imageId = parseInt(parts[0]);
            imageUrl = parts[1];
            if (imageId && !imagesArray.includes(imageId)) {
                imagesArray.push(imageId);
            }
        }

        const imageSrc = isExisting ? `/assets/images/product-image/${imageUrl}` : URL.createObjectURL(file);

        // Créer la carte de prévisualisation
        const previewCard = $('<div>')
            .addClass('col-md-3 col-sm-6 mb-3 image-preview-card')
            .attr('data-image-name', imageUrl);

        const cardContainer = $('<div>')
            .addClass('card h-100 position-relative');

        const img = $('<img>')
            .attr('src', imageSrc)
            .addClass('card-img-top')
            .css({
                height: '160px',
                objectFit: 'cover'
            });

        const cardBody = $('<div>')
            .addClass('card-body p-2');

        const cardFooter = $('<div>')
            .addClass('card-footer bg-white border-top-0 p-2 d-flex justify-content-between align-items-center');

        const fileName = $('<small>')
            .addClass('text-muted text-truncate')
            .css('max-width', '70%')
            .text(typeof file === 'object' ? file.name || imageUrl : imageUrl);

        const deleteBtn = $('<button>')
            .addClass('btn btn-sm btn-outline-danger')
            .html('<i class="fas fa-trash-alt"></i>')
            .on('click', function () {
                previewCard.remove();

                if (isExisting) {
                    if (!deletedImages.includes(imageUrl)) {
                        if (imageUrl != "No_Image_Available.jpg")
                            deletedImages.push(imageUrl);
                    }
                } else {
                    const index = imagesArray.indexOf(file);
                    if (index !== -1) {
                        imagesArray.splice(index, 1);
                    }
                }
            });

        cardFooter.append(fileName, deleteBtn);
        cardContainer.append(img, cardBody, cardFooter);
        previewCard.append(cardContainer);
        container.append(previewCard);
    });
}

function formatTimeDifference(createdAt) {
    const dateCreated = new Date(createdAt);
    const now = new Date();
    const diffInSeconds = Math.floor((now - dateCreated) / 1000);
    if (diffInSeconds < 60) return `${diffInSeconds} secondes`;
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) return `${diffInMinutes} minutes`;
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} heures`;
    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays} jours`;
}
function createFakeFileObject(imageUrl) {
    return {
        name: imageUrl.split('/').pop(), type: 'image/jpeg', url: imageUrl, readAsDataURL: function () {
            return new Promise((resolve) => {
                resolve(imageUrl);

            });
        }
    };
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
function afficherMessageSessionExpiree() {
    $('body').html(`<div class="container" style="margin-top: 6rem !important;
    "><div class="row justify-content-center"><div class="col-md-6"><div class="card"><div class="card-header"><h2 class="text-center">Session expirée.</h2></div><div class="card-body">Votre session a expiré. Veuillez vous reconnecter pour continuer.</div><div class="card-footer text-center"><a href="/login" class="text-muted">Se reconnecter</a><br><a href="/home" class="text-muted">Retour à la page d'accueil</a><br></div></div></div></div></div>`);

}
function toggleMainMenuVisibility() {
    const anyModalOpen = $('.modal.show').length > 0;
    const anyCollapseOpen = $('.collapse.show').not('.noThis').length > 0;

    if (!anyModalOpen && !anyCollapseOpen) {
        $('#mainContainerp').removeClass('d-none');
    } else {
        $('#mainContainerp').addClass('d-none');
    }
}

// Gérer tous les modals
$('.modal').on('shown.bs.modal hidden.bs.modal', toggleMainMenuVisibility);

// Gérer tous les collapses sauf ceux ayant la classe 'noThis'
$('.collapse').on('shown.bs.collapse hidden.bs.collapse', function () {
    toggleMainMenuVisibility();
});


function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
function showNotification(message, type = "info") {
    showToaster(message);
}
function orderStatusSwitcher(status) {
    switch (status) {
        case "pending":
            statusClass = "text-warning";
            statusText = "En attente";
            break;
        case "processing":
            statusClass = "text-info";
            statusText = "En traitement";
            break;
        case "shipped":
            statusClass = "text-primary";
            statusText = "Expédié";
            break;
        case "delivered":
            statusClass = "text-success";
            statusText = "Livré";
            break;
        case "cancelled":
            statusClass = "text-danger";
            statusText = "Annulé";
            break;
        case "returned":
            statusClass = "text-secondary";
            statusText = "Retourné";
            break;
        case "all":
            statusClass = "text-dark";
            statusText = "Tous";
            break;
    }
    return {
        statusClass: statusClass,
        statusText: statusText
    };
}
function renderOrdersTable(orders) {
    $("#ordersTableBody").html("");
    if (orders.length > 0) {
        orders.forEach(order => {
            let status = orderStatusSwitcher(order.client.status);
            let row = `
      <tr data-order-id="${order.client.id}">
        <td class="order-id">${order.client.id}</td>
        <td class="order-client">${order.client.nom_prenom}</td>
        <td class="order-quantity">${order.produits.reduce((sum, p) => sum + p.quantity, 0)}</td>
        <td class="order-status ${status.statusClass}">
            <span class="">
                ${status.statusText}
                <button class="btn btn-sm orderStatusIcon" data-order="${order.client.id}" id="orderStatusIcon" title="Changer le statut">
                    <i class="fa-solid fa-rotate"></i>
                </button>
            </span>
        </td>
        <td class="order-date">${order.client.date}</td>
        <td class="order-actions">
          <button class="btn btn-info btn-sm btn-view-order" data-order-index="${orders.indexOf(order)}">
            <i class="fa fa-eye"></i>
          </button>
          <button class="btn btn-danger btn-sm btn-delete-order" data-order-index="${order.client.id}">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      </tr>`;
            $("#ordersTableBody").append(row);
        });
    } else {
        $("#ordersTableBody").html("<tr><td colspan='7'>Aucune commande trouvée</td></tr>");
    }
}
function loadOrders(page) {
    // Récupérer les filtres
    const searchTerm = $("#searchOrders").val();
    const statusFilter = $("#statusFilter").val();

    $.ajax({
        url: "/orders/paginate",
        type: "POST",
        data: {
            page: page,
            per_page: ordersPerPage,
            search: searchTerm,
            status: statusFilter,
            newOnly: ordersNewOnly
        },
        dataType: "json",
        success: function (response) {
            let data = response.data;
            ordersData = data.orders; // Stocker les données

            renderOrdersTable(data.orders);

            // Mise à jour des boutons de pagination
            ordersCurrentPage = response.data.current_page;
            $("#paginationInfo").text(`Page ${ordersCurrentPage} / ${response.data.total_pages}`);
            $("#orderPrevPage").prop("disabled", ordersCurrentPage === 1);
            $("#orderNextPage").prop("disabled", ordersCurrentPage === response.data.total_pages);
        },
        error: function () {
            showNotification("Erreur de chargement des commandes.", "danger");
        }
    });
}
function loadSubscribersList() {
    $.ajax({
        url: "/subscribers/list",
        type: "post",
        success: function (response) {
            response = JSON.parse(response);
            let html = "";
            response.data.forEach(sub => {
                html += `
                            <tr>
                                <td><input type="checkbox" style='cursor:pointer' class="select-row" data-id="${sub.id}" data-email="${sub.email}"></td>
                                <td>${sub.email}</td>
                                <td>${sub.subscribed_at}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm notify-sub" data-email="${sub.email}">Notifier</button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="${sub.id}">Supprimer</button>
                                </td>
                            </tr>
                        `;
            });
            $("#noSubsTableBody").html(html);
        }
    });
}
function updateSelectedCategories() {
    const categoryDisplay = $('#selectedCategories');
    categoryDisplay.empty();
    selectedCategories.forEach(category => {
        const card = $(`
            <div class="card d-inline-block me-2 mb-3 category-card" style="min-width: 150px;" data-id="${category.id}">
              <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="card-title mb-0">${category.title}</span>
                <button type="button" class="btn-close ms-2 btn-close-sm remove-category" data-id="${category.id}"></button>
              </div>
            </div>
          `);
        categoryDisplay.append(card);
    });
}
function updateCategoryList() {
    $('#categoryList li').each(function () {
        let categoryId = $(this).data('info').id;
        let isSelected = selectedCategories.some(cat => cat.id === categoryId);
        $(this).toggle(!isSelected);

    });
}
function loadOrdersStatistics() {
    page = 1;
    $.ajax({
        url: "/orders/statistics",
        type: "POST",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                ordersData = response.data;
                filterAndRender(); // Filtrer et afficher après chargement
            } else {
                showToaster(response.message);
            }
        },
        error: function () {
            showToaster("Erreur lors du chargement des données.", "danger");
        }
    });
}
function filterAndRender() {
    // Vérifier si des données sont disponibles
    if (!ordersData || ordersData.length === 0) {
        $('#ordersTableBody2').empty().append('<tr><td colspan="5" class="text-center">Aucune donnée disponible</td></tr>');
        $('#orderStaTotal2').text('0.00 DH');
        $('#paginationInfo2').text('Page 0 / 0');
        $('#orderPrevPage2').prop('disabled', true);
        $('#orderNextPage2').prop('disabled', true);
        return;
    }

    // Copie des données pour filtrage
    let filtered = [...ordersData];

    // Application du filtre de recherche
    const search = $('#searchOrders2').val().toLowerCase();
    if (search) {
        filtered = filtered.filter(order =>
            (order.name && order.name.toLowerCase().includes(search)) ||
            (order.id && order.id.toString().includes(search))
        );
    }

    // Application du filtre de statut
    const selectedStatuses = $('.cmdStat2:checked').map(function () {
        return this.value;
    }).get();

    if (selectedStatuses.length > 0) {
        filtered = filtered.filter(order => selectedStatuses.includes(order.status));
    }

    // Application du filtre de période
    const period = $('#filterPeriod2').val();
    filtered = applyDateFilter(filtered, period);

    // Calcul du total des commandes filtrées
    const totalAmount = filtered.reduce((sum, order) => sum + parseFloat(order.total || 0), 0);
    $('#orderStaTotal2').text(`${totalAmount.toFixed(2)} DH`);

    // Pagination des résultats
    renderPagination(filtered);

    // Mettre à jour le nombre total de commandes pour l'impression
    $('#printTotalOrders').text(filtered.length);
    $('#printTotalAmount').text(`${totalAmount.toFixed(2)} DH`);

    // Stocker les données filtrées pour l'impression
    window.filteredOrdersForPrint = filtered;
}
function applyDateFilter(orders, period) {
    if (!period) return orders;

    const now = new Date();

    return orders.filter(order => {
        if (!order.date) return true;

        try {
            const orderDate = new Date(order.date + 'T00:00:00');

            switch (period) {
                case 'day':
                    return orderDate.toDateString() === now.toDateString();
                case 'week':
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    return orderDate >= startOfWeek;
                case 'month':
                    return orderDate.getMonth() === now.getMonth() &&
                        orderDate.getFullYear() === now.getFullYear();
                case 'year':
                    return orderDate.getFullYear() === now.getFullYear();
                case 'custom':
                    const startInput = $('#startDate2').val();
                    const endInput = $('#endDate2').val();

                    if (!startInput || !endInput) return true;

                    const start = new Date(startInput + 'T00:00:00');
                    const end = new Date(endInput + 'T23:59:59');

                    return orderDate >= start && orderDate <= end;
                default:
                    return true;
            }
        } catch (e) {
            console.error("Erreur lors du traitement de la date:", order.date, e);
            return true; // Inclure en cas d'erreur
        }
    });
}
function renderPagination(filtered) {
    const totalPages = Math.ceil(filtered.length / ordersPerPage) || 1;

    // Ajustement de la page courante si nécessaire
    if (page > totalPages) {
        page = totalPages;
    }

    // Extraction des données pour la page courante
    const start = (page - 1) * ordersPerPage;
    const paginated = filtered.slice(start, start + ordersPerPage);

    // Mise à jour du corps du tableau
    renderTableBody(paginated);

    // Mise à jour des contrôles de pagination
    $('#paginationInfo2').text(`Page ${page} / ${totalPages}`);
    $('#orderPrevPage2').prop('disabled', page <= 1);
    $('#orderNextPage2').prop('disabled', page >= totalPages);
}
function renderTableBody(orders) {
    const tbody = $('#ordersTableBody2');
    tbody.empty();

    if (orders.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center">Aucun résultat trouvé</td></tr>');
        return;
    }

    orders.forEach(order => {
        const stat = orderStatusSwitcher(order.status);
        tbody.append(`
        <tr>
            <td>${order.id || '-'}</td>
            <td>${order.name || '-'}</td>
            <td><span class="${stat.statusClass}">${stat.statusText}</span></td>
            <td>${order.date || '-'}</td>
            <td>${order.total || '0.00'} DH</td>
        </tr>
    `);
    });
}
function loadStockData() {
    if (stockState.isLoading) return;

    stockState.isLoading = true;
    stockShowLoadingIndicator();

    $.ajax({
        url: API.FETCH_STOCK,
        type: 'POST',
        dataType: 'json',
        cache: false,
        timeout: 10000, // 10s timeout
        success: function (response) {
            if (!response || !response.data) {
                stockShowError('Format de réponse invalide');
                return;
            }

            stockState.allStockData = response.data.map(product => ({
                ...product,
                status: stockGetProductStatus(product.instock),
                stock_update: product.stock_update || 'N/A'
            }));

            stockRenderTable();
            stockUpdatePagination();
        },
        error: function (xhr, status, error) {
            const errorMsg = xhr.responseJSON?.message || error || 'Erreur de serveur';
            stockShowError(`Erreur lors du chargement des données: ${errorMsg}`);
        },
        complete: function () {
            stockState.isLoading = false;
        }
    });
}
function stockShowLoadingIndicator() {
    stockProduts.tableBody.html('<tr><td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Chargement des données...</td></tr>');
}
function stockShowError(message) {
    stockProduts.tableBody.html(`<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>${message}</td></tr>`);
}
function stockGetProductStatus(instock) {
    if (instock <= 0) return 'out_of_stock';
    if (instock < STOCK_THRESHOLD) return 'low_stock';
    return 'in_stock';
}
function stockGetStatusBadgeInfo(status) {
    switch (status) {
        case 'in_stock':
            return {
                class: 'bg-success', text: 'En stock'
            };
        case 'out_of_stock':
            return {
                class: 'bg-danger', text: 'Pas en stock'
            };
        case 'low_stock':
            return {
                class: 'bg-warning text-dark', text: 'Stock faible'
            };
        default:
            return {
                class: 'bg-secondary', text: 'Inconnu'
            };
    }
}
function stockFilterData() {
    let filtered = stockState.allStockData;

    // Filtre par statut
    if (stockState.currentFilter !== 'all') {
        filtered = filtered.filter(product => product.status === stockState.currentFilter);
    }

    // Filtre par recherche
    if (stockState.currentSearch) {
        const searchTerm = stockState.currentSearch.toLowerCase().trim();
        filtered = filtered.filter(product =>
            product.title.toLowerCase().includes(searchTerm) ||
            product.id.toString().includes(searchTerm)
        );
    }
    return filtered;
}
function stockRenderTable() {
    const filteredData = stockFilterData();
    const start = (stockState.currentPage - 1) * stockState.rowsPerPage;
    const end = start + stockState.rowsPerPage;
    const paginatedData = filteredData.slice(start, end);

    stockProduts.tableBody.empty();

    if (paginatedData.length === 0) {
        stockProduts.tableBody.html('<tr><td colspan="7" class="text-center">Aucun produit trouvé</td></tr>');
        return;
    }

    const rows = paginatedData.map(product => {
        const badge = stockGetStatusBadgeInfo(product.status);

        return `
      <tr data-product-id="${product.id}">
        <td>${product.id}</td>
        <td class="text-truncate" style="max-width: 250px;" title="${escapeHtml(product.title)}">${escapeHtml(product.title)}</td>
        <td>
          <input type="number" class="form-control stock-input" min="0" value="${product.instock}">
        </td>
        <td>${product.outstock}</td>
        <td><span class="badge ${badge.class}">${badge.text}</span></td>
        <td>${product.stock_update}</td>
        <td>
          <button type="button" class="btn btn-primary btn-sm save-stock" disabled data-id="${product.id}">
            <i class="fas fa-save"></i> Confirmer
          </button>
        </td>
      </tr>
    `;
    }).join('');

    stockProduts.tableBody.html(rows);
    stockUpdatePaginationInfo();
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function stockUpdatePagination() {
    const filteredData = stockFilterData();
    const totalPages = Math.ceil(filteredData.length / stockState.rowsPerPage) || 1;

    stockProduts.pagination.empty();

    if (totalPages <= 1) return;

    // Plage de pages à afficher
    const maxPages = 5;
    let startPage = Math.max(1, stockState.currentPage - Math.floor(maxPages / 2));
    let endPage = Math.min(totalPages, startPage + maxPages - 1);

    // Ajuster si nous sommes près de la fin
    if (endPage - startPage + 1 < maxPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }

    // Bouton Première page
    stockProduts.pagination.append(`
    <li class="page-item ${stockState.currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="1" aria-label="Première page">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
   `);

    // Bouton Précédent
    stockProduts.pagination.append(`
    <li class="page-item ${stockState.currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${stockState.currentPage - 1}" aria-label="Précédent">
        <span aria-hidden="true">&lsaquo;</span>
      </a>
    </li>
    `);

    // Pages
    for (let i = startPage; i <= endPage; i++) {
        stockProduts.pagination.append(`
      <li class="page-item ${i === stockState.currentPage ? 'active' : ''}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `);
    }

    // Bouton Suivant
    stockProduts.pagination.append(`
    <li class="page-item ${stockState.currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${stockState.currentPage + 1}" aria-label="Suivant">
        <span aria-hidden="true">&rsaquo;</span>
      </a>
    </li>
    `);

    // Bouton Dernière page
    stockProduts.pagination.append(`
    <li class="page-item ${stockState.currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${totalPages}" aria-label="Dernière page">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
    `);
}
function stockUpdatePaginationInfo() {
    const filteredData = stockFilterData();
    const totalItems = filteredData.length;

    if (totalItems === 0) {
        stockProduts.paginationInfo.text('Aucun produit trouvé');
        return;
    }

    const start = (stockState.currentPage - 1) * stockState.rowsPerPage + 1;
    const end = Math.min(start + stockState.rowsPerPage - 1, totalItems);

    stockProduts.paginationInfo.text(`Affichage ${start}-${end} sur ${totalItems} produits`);
}
function handleSaveStock() {
    const button = $(this);
    const row = button.closest('tr');
    const input = row.find('.stock-input');
    const id = button.data('id');
    const newStock = parseInt(input.val(), 10);

    if (isNaN(newStock) || newStock < 0) {
        showToaster('Veuillez entrer une valeur de stock valide (nombre positif ou zéro).');
        input.addClass('is-invalid');
        return;
    }

    // Désactiver le bouton pendant la sauvegarde
    button.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sauvegarde...');

    $.ajax({
        type: "POST",
        url: API.UPDATE_STOCK,
        data: {
            'id': id,
            'newstock': newStock
        },
        dataType: 'json',
        success: function (response) {
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    showToaster('Erreur dans la réponse du serveur');
                    return;
                }
            }

            if (response.success) {
                button.removeClass('btn-primary')
                    .addClass('btn-success')
                    .html('<i class="fas fa-check"></i> Sauvé');

                // Mettre à jour les données localement sans recharger toute la table
                const productIndex = stockState.allStockData.findIndex(p => p.id === id);
                if (productIndex !== -1) {
                    stockState.allStockData[productIndex].instock = newStock;
                    stockState.allStockData[productIndex].status = stockGetProductStatus(newStock);
                    stockState.allStockData[productIndex].stock_update = formatDate(new Date());

                    // Mettre à jour le badge de statut directement
                    const badge = stockGetStatusBadgeInfo(stockState.allStockData[productIndex].status);
                    row.find('td:nth-child(5) .badge')
                        .removeClass()
                        .addClass(`badge ${badge.class}`)
                        .text(badge.text);

                    // Mettre à jour la date de dernière mise à jour
                    row.find('td:nth-child(6)').text(stockState.allStockData[productIndex].stock_update);
                }

                showToaster(response.message);

                // Réactiver le bouton après un délai
                setTimeout(() => {
                    button.prop('disabled', false)
                        .removeClass('btn-success')
                        .addClass('btn-primary')
                        .html('<i class="fas fa-save"></i> Confirmer');
                }, 3000);
            } else {
                button.prop('disabled', false)
                    .html('<i class="fas fa-save"></i> Confirmer');
                showToaster(response.message || 'Erreur lors de la sauvegarde');
            }
        },
        error: function (xhr, status, error) {
            button.prop('disabled', false)
                .html('<i class="fas fa-save"></i> Confirmer');
            showToaster('Erreur serveur: ' + (xhr.responseJSON?.message || error));
        }
    });
}
function formatDate(date) {
    return date.toLocaleString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}
function validateNumericInput(input) {
    const value = input.val();

    // Si vide, mettre à 0
    if (value === '') {
        input.val(0);
    }

    // Forcer une valeur positive
    if (parseInt(value, 10) < 0) {
        input.val(0);
    }

    // Retirer la classe d'invalidation si présente
    input.removeClass('is-invalid');
}
function stockHandleSearch() {
    stockState.currentSearch = stockProduts.searchInput.val();
    stockState.currentPage = 1;
    stockRenderTable();
    stockUpdatePagination();
}
function stockClearSearch() {
    stockProduts.searchInput.val('');
    stockState.currentSearch = '';
    stockState.currentPage = 1;
    stockRenderTable();
    stockUpdatePagination();
}
function stockHandleFilterChange() {
    stockState.currentFilter = $(this).val();
    stockState.currentPage = 1;
    stockRenderTable();
    stockUpdatePagination();
}
function stockHandleRowsPerPageChange() {
    stockState.rowsPerPage = parseInt($(this).val(), 10);
    stockState.currentPage = 1;
    stockRenderTable();
    stockUpdatePagination();
}
function stockHandlePaginationClick(e) {
    e.preventDefault();
    const targetPage = parseInt($(this).data('page'), 10);

    if (isNaN(targetPage) || targetPage === stockState.currentPage) return;

    stockState.currentPage = targetPage;
    stockRenderTable();
    stockUpdatePagination();

    // Scroll au haut du tableau
    $('html, body').animate({
        scrollTop: stockProduts.tableBody.offset().top - 100
    }, 300);
}
function stockDebounce(func, wait) {
    let timeout;
    return function () {
        const context = this,
            args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}
function stockPreparePrint() {
    // Récupérer les données filtrées
    const filteredData = stockFilterData();

    if (filteredData.length === 0) {
        showToaster("Aucune donnée à imprimer", "warning");
        return;
    }

    // Récupérer les informations de filtrage actuelles
    const searchQuery = $('#stockSearchInput').val();
    const currentFilter = $('#stockStatusFilter').val();
    let filterText = "Tous les statuts";

    switch (currentFilter) {
        case 'in_stock':
            filterText = "En stock";
            break;
        case 'out_of_stock':
            filterText = "Pas en stock";
            break;
        case 'low_stock':
            filterText = "Stock faible";
            break;
    }

    // Calculer les totaux
    const totalProducts = filteredData.length;
    const inStockCount = filteredData.filter(p => p.status === 'in_stock').length;
    const lowStockCount = filteredData.filter(p => p.status === 'low_stock').length;
    const outOfStockCount = filteredData.filter(p => p.status === 'out_of_stock').length;
    const totalStock = filteredData.reduce((sum, product) => sum + parseInt(product.instock || 0, 10), 0);
    const totalDelivered = filteredData.reduce((sum, product) => sum + parseInt(product.outstock || 0, 10), 0);

    // Créer le contenu de la page d'impression
    let printContent = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>Rapport de Stock</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            .report-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .report-title {
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .report-date {
                font-size: 14px;
                margin-bottom: 15px;
            }
            .filter-info {
                font-size: 14px;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 5px;
            }
            .filter-row {
                margin-bottom: 5px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .total-row {
                font-weight: bold;
            }
            .status-badge {
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 12px;
                display: inline-block;
                min-width: 80px;
                text-align: center;
            }
            .status-in_stock { background-color: #28a745; color: #fff; }
            .status-out_of_stock { background-color: #dc3545; color: #fff; }
            .status-low_stock { background-color: #ffc107; color: #000; }
            .report-footer {
                margin-top: 30px;
                font-size: 12px;
                text-align: center;
            }
            .summary-section {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            .summary-card {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
                text-align: center;
            }
            .summary-title {
                font-size: 12px;
                color: #666;
            }
            .summary-value {
                font-size: 18px;
                font-weight: bold;
                margin-top: 5px;
            }
            .title-column {
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            @media print {
                .no-print {
                    display: none;
                }
                body {
                    padding: 0;
                    margin: 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <div class="report-title">Rapport de Stock</div>
            <div class="report-date">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</div>
        </div>
        
        <div class="filter-info">
            <div class="filter-row"><strong>Filtre:</strong> ${filterText}</div>
            ${searchQuery ? `<div class="filter-row"><strong>Recherche:</strong> ${searchQuery}</div>` : ''}
            <div class="filter-row"><strong>Nombre de produits:</strong> ${totalProducts}</div>
        </div>
        
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-title">Produits en stock</div>
                    <div class="summary-value">${inStockCount}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Stock faible</div>
                    <div class="summary-value">${lowStockCount}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Pas en stock</div>
                    <div class="summary-value">${outOfStockCount}</div>
                </div>
            </div>
        </div>
        <div class="no-print" style="text-align: center; margin:25px auto; ">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-print" style="margin-right: 5px;"></i> Imprimer
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-times" style="margin-right: 5px;"></i> Fermer
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>En stock</th>
                    <th>Livré</th>
                    <th>Statut</th>
                    <th>Dernière mise à jour</th>
                </tr>
            </thead>
            <tbody>
    `;

    // Ajouter les lignes de données
    filteredData.forEach(product => {
        let statusClass = '';
        let statusText = '';

        switch (product.status) {
            case 'in_stock':
                statusClass = 'status-in_stock';
                statusText = 'En stock';
                break;
            case 'out_of_stock':
                statusClass = 'status-out_of_stock';
                statusText = 'Pas en stock';
                break;
            case 'low_stock':
                statusClass = 'status-low_stock';
                statusText = 'Stock faible';
                break;
            default:
                statusClass = '';
                statusText = 'Inconnu';
        }

        printContent += `
        <tr>
            <td>${product.id || '-'}</td>
            <td class="title-column" title="${escapeHtml(product.title)}">${escapeHtml(product.title) || '-'}</td>
            <td>${product.instock || '0'}</td>
            <td>${product.outstock || '0'}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${product.stock_update || 'N/A'}</td>
        </tr>
    `;
    });

    // Ajouter les totaux et fermer le HTML
    printContent += `
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" style="text-align:right">Total:</td>
                    <td>${totalStock}</td>
                    <td>${totalDelivered}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Imprimer
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Fermer
            </button>
        </div>
    </body>
    </html>
    `;

    // Ouvrir une nouvelle fenêtre pour l'impression
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();

    // Attendre que le contenu soit chargé puis imprimer
    printWindow.onload = function () {
        // Ne pas lancer l'impression automatiquement pour permettre à l'utilisateur de voir l'aperçu
        // Laissons l'utilisateur cliquer sur le bouton Imprimer
    };
}
function printOrdersReport() {
    // Récupérer les données filtrées
    const filteredData = window.filteredOrdersForPrint || [];

    if (filteredData.length === 0) {
        showToaster("Aucune donnée à imprimer");
        return;
    }

    // Récupérer les informations de filtrage actuelles
    const searchQuery = $('#searchOrders2').val();
    const period = $('#filterPeriod2').val();
    let periodText = "Toutes les périodes";

    switch (period) {
        case 'day':
            periodText = "Aujourd'hui";
            break;
        case 'week':
            periodText = "Cette semaine";
            break;
        case 'month':
            periodText = "Ce mois";
            break;
        case 'year':
            periodText = "Cette année";
            break;
        case 'custom':
            const startDate = $('#startDate2').val();
            const endDate = $('#endDate2').val();
            periodText = `Du ${startDate} au ${endDate}`;
            break;
    }

    // Récupérer les statuts sélectionnés
    const selectedStatuses = $('.cmdStat2:checked').map(function () {
        return orderStatusSwitcher(this.value).statusText;
    }).get().join(", ") || "Tous les statuts";

    // Calculer le total
    const totalAmount = filteredData.reduce((sum, order) => sum + parseFloat(order.total || 0), 0);

    // Créer le contenu de la page d'impression
    let printContent = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>Rapport des Commandes</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            .report-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .report-title {
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .report-date {
                font-size: 14px;
                margin-bottom: 15px;
            }
            .filter-info {
                font-size: 14px;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 5px;
            }
            .filter-row {
                margin-bottom: 5px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .total-row {
                font-weight: bold;
            }
            .status-badge {
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 12px;
            }
            .status-pending { background-color: #ffc107; color: #000; }
            .status-processing { background-color: #17a2b8; color: #000; }
            .status-shipped { background-color: #007bff; color: #fff; }
            .status-delivered { background-color: #28a745; color: #fff; }
            .status-cancelled { background-color: #dc3545; color: #fff; }
            .status-returned { background-color: #6c757d; color: #fff; }
            .report-footer {
                margin-top: 30px;
                font-size: 12px;
                text-align: center;
            }
            @media print {
                .no-print {
                    display: none;
                }
                body {
                    padding: 0;
                    margin: 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <div class="report-title">Rapport des Commandes</div>
            <div class="report-date">Généré le ${new Date().toLocaleDateString()} à ${new Date().toLocaleTimeString()}</div>
        </div>
        
        <div class="filter-info">
            <div class="filter-row"><strong>Période:</strong> ${periodText}</div>
            <div class="filter-row"><strong>Statuts:</strong> ${selectedStatuses}</div>
            ${searchQuery ? `<div class="filter-row"><strong>Recherche:</strong> ${searchQuery}</div>` : ''}
            <div class="filter-row"><strong>Nombre de commandes:</strong> ${filteredData.length}</div>
            <div class="filter-row"><strong>Total :</strong> ${totalAmount.toFixed(2)} DH</div>
        </div>
        
        <div class="no-print" style="text-align: center; margin:25px auto; ">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-print" style="margin-right: 5px;"></i> Imprimer
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-times" style="margin-right: 5px;"></i> Fermer
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
    `;

    // Ajouter les lignes de données
    filteredData.forEach(order => {
        const stat = orderStatusSwitcher(order.status);
        const statusClass = `status-${order.status}`;

        printContent += `
        <tr>
            <td>${order.id || '-'}</td>
            <td>${order.name || '-'}</td>
            <td><span class="status-badge ${statusClass}">${stat.statusText}</span></td>
            <td>${order.date || '-'}</td>
            <td>${order.total || '0.00'} DH</td>
        </tr>
    `;
    });

    // Ajouter la ligne de total et fermer le HTML
    printContent += `
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" style="text-align:right">Total:</td>
                    <td>${totalAmount.toFixed(2)} DH</td>
                </tr>
            </tfoot>
        </table>
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-print" style="margin-right: 5px;"></i> Imprimer
            </button>
            <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-times" style="margin-right: 5px;"></i> Fermer
            </button>
        </div>
    </body>
    </html>
    `;

    // Ouvrir une nouvelle fenêtre pour l'impression
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
}
//Section
function addNewList() {
    const listCount = $('#listsContainer .card').length;

    // Vérifier le nombre maximum de listes (4)
    if (listCount >= 4) {
        showToaster('Vous avez atteint le nombre maximum de listes (4).');
        return;
    }

    const listId = Date.now(); // ID unique pour la liste
    const newList = `
    <div class="card mb-3" data-list-id="${listId}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <input type="text" class="form-control list-title" placeholder="Titre de la liste" maxlength="50">
        <button type="button" class="btn btn-danger btn-sm remove-list ms-2">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    <div class="card-body">
        <ul class="list-group list-items-container">
        </ul>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-primary add-list-item">
                <i class="fa fa-plus-circle" aria-hidden="true"></i>
                Ajouter un élément
            </button>
        </div>
    </div>
    </div>
    `;
    $('#listsContainer').append(newList);

    // Ajouter deux éléments par défaut à la liste
    const $newListContainer = $(`[data-list-id="${listId}"] .list-items-container`);
    addListItem($newListContainer);
    addListItem($newListContainer);
}
function updateSectionsOrder(sectionIds) {
    $.ajax({
        type: "POST",
        url: "/sections/update-order",
        contentType: "application/json",
        data: JSON.stringify({
            page: currentPage,
            sections: sectionIds
        }),
        success: function (response) {
            response = JSON.parse(response);
            if (response.success) {
                // Pas besoin de rafraîchir, l'ordre visuel est déjà mis à jour
                showToaster(response.message);
            } else {
                // En cas d'erreur, rafraîchir pour rétablir l'ordre correct
                $(".custom-sections-tab.active").trigger("click");
                showToaster(response.message);
            }
        },
        error: function () {
            // En cas d'erreur, rafraîchir pour rétablir l'ordre correct
            $(".custom-sections-tab.active").trigger("click");
            showToaster("Erreur de connexion au serveur");
        }
    });
}
function addListItem($listContainer) {
    const itemCount = $listContainer.find('.list-group-item').length;

    // Vérifier le nombre maximum d'éléments (5)
    if (itemCount >= 5) {
        showToaster('Vous avez atteint le nombre maximum d\'éléments (5) pour cette liste.');
        return;
    }

    const itemId = Date.now(); // ID unique pour l'élément
    const newItem = `
    <li class="list-group-item d-flex align-items-center" data-item-id="${itemId}">
    <div class="flex-grow-1">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control item-text" placeholder="Texte de l'élément" maxlength="50">
            <div class="input-group-text">
                <div class="form-check form-switch">
                    <input class="form-check-input is-link-switch" type="checkbox" id="isLink${itemId}">
                    <label class="form-check-label" for="isLink${itemId}">Lien</label>
                </div>
            </div>
        </div>
        <input type="text" class="form-control form-control-sm mt-1 item-url d-none" placeholder="URL du lien">
    </div>
    <button type="button" class="btn btn-danger btn-sm remove-item ms-2">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </button>
    </li>
    `;
    $listContainer.append(newItem);
}
function loadModel1EditForm(sectionDetails) {
    // Réinitialiser le conteneur de listes
    $('#listsContainer').empty();

    // Tenter de récupérer et de parser les données
    const sectionData = sectionDetails.data || {};
    // Si c'est déjà un objet, l'utiliser tel quel, sinon essayer de parser
    const data = typeof sectionData === 'object' && !Array.isArray(sectionData) ?
        sectionData :
        JSON.parse(typeof sectionData === 'string' ? sectionData : '{}');

    // Extraire les listes du modèle
    const lists = data.lists || [];

    // Si aucune liste n'est présente, en ajouter deux par défaut
    if (lists.length === 0) {
        addNewList();
        addNewList();
    } else {
        // Ajouter chaque liste depuis les données
        lists.forEach(function (list) {
            const listId = Date.now() + Math.floor(Math.random() * 1000);
            const newList = `
    <div class="card mb-3" data-list-id="${listId}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <input type="text" class="form-control list-title" placeholder="Titre de la liste" maxlength="50" value="${list.title || ''}">
            <button type="button" class="btn btn-danger btn-sm remove-list ms-2">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
        <div class="card-body">
            <ul class="list-group list-items-container">
            </ul>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-primary add-list-item">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    Ajouter un élément
                </button>
            </div>
        </div>
    </div>
    `;
            $('#listsContainer').append(newList);

            // Récupérer le conteneur de la liste
            const $listContainer = $(`[data-list-id="${listId}"] .list-items-container`);

            // Ajouter les éléments de la liste
            const items = list.items || [];
            if (items.length === 0) {
                addListItem($listContainer);
                addListItem($listContainer);
            } else {
                items.forEach(function (item) {
                    const itemId = Date.now() + Math.floor(Math.random() * 1000);
                    const isLink = item.isLink === true || item.isLink === "true" || item.isLink === 1 || item.isLink === "1";

                    const newItem = `
                    <li class="list-group-item d-flex align-items-center" data-item-id="${itemId}">
                        <div class="flex-grow-1">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control item-text" placeholder="Texte de l'élément" maxlength="50" value="${item.text || ''}">
                                <div class="input-group-text">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input is-link-switch" type="checkbox" id="isLink${itemId}" ${isLink ? 'checked' : ''}>
                                        <label class="form-check-label" for="isLink${itemId}">Lien</label>
                                    </div>
                                </div>
                            </div>
                            <input type="text" class="form-control form-control-sm mt-1 item-url ${isLink ? '' : 'd-none'}" placeholder="URL du lien" value="${item.url || ''}">
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-item ms-2">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </li>
                    `;
                    $listContainer.append(newItem);
                });

            }
        });
    }

    // Stocker l'ID de la section pour la mise à jour
    currentSectionId = sectionDetails.id;
    currentModelId = parseInt(sectionDetails.model);
}
function loadModel2EditForm(sectionDetails) {
    // Analyser les données de la section
    const sectionData = sectionDetails.data || {};
    // Si c'est déjà un objet, l'utiliser tel quel, sinon essayer de parser
    const data = typeof sectionData === 'object' && !Array.isArray(sectionData) ?
        sectionData :
        JSON.parse(typeof sectionData === 'string' ? sectionData : '{}');

    // Remplir les champs du formulaire
    $('#model2SmallTitle').val(data.smallTitle || '');
    $('#model2LargeTitle').val(data.largeTitle || '');
    $('#model2Description').val(data.description || '');
    $('#model2ImageUrl').val(data.imageUrl || '');
    $('#model2LinkUrl').val(data.linkUrl || '');
    $('#model2LinkText').val(data.linkText || '');

    // Stocker l'ID de la section pour la mise à jour
    currentSectionId = sectionDetails.id;
    currentModelId = parseInt(sectionDetails.model);
}
function loadModel3EditForm(sectionDetails) {
    // Tenter de récupérer et de parser les données
    const sectionData = sectionDetails.data || {};
    // Si c'est déjà un objet, l'utiliser tel quel, sinon essayer de parser
    let data = {};
    try {
        data = typeof sectionData === 'object' && !Array.isArray(sectionData) ?
            sectionData :
            JSON.parse(typeof sectionData === 'string' ? sectionData : '{}');
    } catch (e) {
        console.error("Erreur de parsing des données:", e);
        data = {};
    }
    if (typeof data === 'string') {
        data = JSON.parse(data);
    }

    // Remplir le titre
    $('#model3Title').val(data.title || '');

    // Extraire les features du modèle
    const features = data.features || [];

    // Si aucun élément n'est présent, en ajouter un par défaut
    if (features.length === 0) {
        addFeature();
    } else {
        // Ajouter chaque élément depuis les données
        features.forEach(function (feature, index) {
            addFeatureWithData(feature, index);
        });

        // Attacher les événements de suppression
        $('#featuresContainer .remove-feature').on('click', function () {
            $(this).closest('.feature-item').remove();
            updateFeatureNumbers();
        });
    }

    // Stocker l'ID de la section pour la mise à jour
    currentSectionId = sectionDetails.id;
    currentModelId = parseInt(sectionDetails.model);
}
function loadModel4EditForm(sectionDetails) {
    // Réinitialiser le conteneur
    $('#imagesContainer').html("");

    // Tenter de récupérer et de parser les données
    const sectionData = sectionDetails.data || {};
    // Si c'est déjà un objet, l'utiliser tel quel, sinon essayer de parser
    let data = {};
    try {
        data = typeof sectionData === 'object' && !Array.isArray(sectionData) ?
            sectionData :
            JSON.parse(typeof sectionData === 'string' ? sectionData : '{}');
    } catch (e) {
        console.error("Erreur de parsing des données:", e);
        data = {};
    }
    // Remplir les champs du formulaire
    $('#model4Title').val(data.title || '');
    $('#model4Description').val(data.description || '');

    // Extraire les images du modèle
    const images = data.images || [];

    // Si aucune image n'est présente, en ajouter une par défaut
    if (images.length === 0) {
        addImage();
    } else {
        // Ajouter chaque image depuis les données
        images.forEach(function (image, index) {
            addImageWithData(image, index);
        });

        // Attacher les événements de suppression
        $('#imagesContainer .remove-image').on('click', function () {
            $(this).closest('.image-item').remove();
            updateImageNumbers();
        });
    }

    // Stocker l'ID de la section pour la mise à jour
    currentSectionId = sectionDetails.id;
    currentModelId = parseInt(sectionDetails.model);
}
function addFeatureWithData(feature, index) {
    const featureId = Date.now() + index;
    const newFeature = `
<div class="card mb-3 feature-item" data-feature-id="${featureId}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Élément #${index + 1}</strong>
        <button type="button" class="btn btn-danger btn-sm remove-feature">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="featureIcon${featureId}" class="form-label">Icône (optionnelle)</label>
            <input type="text" class="form-control feature-icon" id="featureIcon${featureId}" placeholder="Nom de l'icône (ex: fa-star)" value="${feature.icon || ''}">
            <small class="text-muted">Entrez un nom d'icône Font Awesome (ex: fa-star, fa-check)</small>
        </div>
        <div class="mb-3">
            <label for="featureTitle${featureId}" class="form-label">Titre</label>
            <input type="text" class="form-control feature-title" id="featureTitle${featureId}" placeholder="Titre de l'élément" maxlength="50" required value="${feature.title || ''}">
        </div>
        <div class="mb-3">
            <label for="featureDesc${featureId}" class="form-label">Description</label>
            <textarea class="form-control feature-description" id="featureDesc${featureId}" rows="2" maxlength="200" required>${feature.description || ''}</textarea>
        </div>
    </div>
</div>
`;
    $('#featuresContainer').append(newFeature);
}
function addImageWithData(image, index) {
    const imageId = Date.now() + index;
    const newImage = `
<div class="card mb-3 image-item" data-image-id="${imageId}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Image #${index + 1}</strong>
        <button type="button" class="btn btn-danger btn-sm remove-image">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="imageUrl${imageId}" class="form-label">URL de l'image</label>
            <input type="text" class="form-control image-url" id="imageUrl${imageId}" placeholder="https://..." required value="${image.url || ''}">
        </div>
        <div class="mb-3">
            <label for="imageCaption${imageId}" class="form-label">Légende (optionnelle)</label>
            <input type="text" class="form-control image-caption" id="imageCaption${imageId}" placeholder="Légende de l'image" maxlength="100" value="${image.caption || ''}">
        </div>
    </div>
</div>
`;
    $('#imagesContainer').append(newImage);
}
function addFeature() {
    const featureCount = $('#featuresContainer .feature-item').length;

    // Vérifier le nombre maximum d'éléments (4)
    if (featureCount >= 4) {
        showToaster('Vous avez atteint le nombre maximum d\'éléments (4).');
        return;
    }

    const featureId = Date.now(); // ID unique
    const newFeature = `
<div class="card mb-3 feature-item" data-feature-id="${featureId}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Élément #${featureCount + 1}</strong>
        <button type="button" class="btn btn-danger btn-sm remove-feature">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="featureIcon${featureId}" class="form-label">Icône (optionnelle)</label>
            <input type="text" class="form-control feature-icon" id="featureIcon${featureId}" placeholder="Nom de l'icône (ex: fa-star)">
            <small class="text-muted">Entrez un nom d'icône Font Awesome (ex: fa-star, fa-check)</small>
        </div>
        <div class="mb-3">
            <label for="featureTitle${featureId}" class="form-label">Titre</label>
            <input type="text" class="form-control feature-title" id="featureTitle${featureId}" placeholder="Titre de l'élément" maxlength="50" required>
        </div>
        <div class="mb-3">
            <label for="featureDesc${featureId}" class="form-label">Description</label>
            <textarea class="form-control feature-description" id="featureDesc${featureId}" rows="2" maxlength="200" required></textarea>
        </div>
    </div>
</div>
`;
    $('#featuresContainer').append(newFeature);

    // Associer l'événement de suppression au nouveau bouton
    $('#featuresContainer .feature-item:last-child .remove-feature').on('click', function () {
        $(this).closest('.feature-item').remove();
        updateFeatureNumbers();
    });
}
function addImage() {
    const imageCount = $('#imagesContainer .image-item').length;

    // Vérifier le nombre maximum d'images (6)
    if (imageCount >= 6) {
        showToaster('Vous avez atteint le nombre maximum d\'images (6).');
        return;
    }

    const imageId = Date.now(); // ID unique
    const newImage = `
<div class="card mb-3 image-item" data-image-id="${imageId}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Image #${imageCount + 1}</strong>
        <button type="button" class="btn btn-danger btn-sm remove-image">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="imageUrl${imageId}" class="form-label">URL de l'image</label>
            <input type="text" class="form-control image-url" id="imageUrl${imageId}" placeholder="https://..." required>
        </div>
        <div class="mb-3">
            <label for="imageCaption${imageId}" class="form-label">Légende (optionnelle)</label>
            <input type="text" class="form-control image-caption" id="imageCaption${imageId}" placeholder="Légende de l'image" maxlength="100">
        </div>
    </div>
</div>
`;
    $('#imagesContainer').append(newImage);

    // Associer l'événement de suppression au nouveau bouton
    $('#imagesContainer .image-item:last-child .remove-image').on('click', function () {
        $(this).closest('.image-item').remove();
        updateImageNumbers();
    });
}
function updateFeatureNumbers() {
    $('#featuresContainer .feature-item').each(function (index) {
        $(this).find('strong').text(`Élément #${index + 1}`);
    });
}
function updateImageNumbers() {
    $('#imagesContainer .image-item').each(function (index) {
        $(this).find('strong').text(`Image #${index + 1}`);
    });
}
/**
 * Affiche les statuts des différentes opérations avec une interface visuelle améliorée
 * @param {Array} operationStatus - Tableau des statuts d'opération
 */
function displayOperationStatus(operationStatus) {
    if (!operationStatus || !Array.isArray(operationStatus) || operationStatus.length === 0) {
        return;
    }

    // Créer le conteneur des notifications s'il n'existe pas
    if (!$('#status-dashboard').length) {
        $('body').append(`
            <div id="status-dashboard" 
                 class="position-fixed bottom-0 start-0 p-3" 
                 style="z-index: 1060; max-width: 90%; width: 450px;">
            </div>
        `);
    }

    // Identifier la tendance générale du statut
    const statusCounts = operationStatus.reduce((counts, op) => {
        counts[op.status] = (counts[op.status] || 0) + 1;
        return counts;
    }, {});

    const totalOps = operationStatus.length;
    const successRate = Math.round(((statusCounts.success || 0) / totalOps) * 100);

    // Déterminer l'icône et la couleur principale
    let mainIcon, mainColor, badgeClass;
    if (successRate === 100) {
        mainIcon = '<i class="fas fa-check-circle fa-2x"></i>';
        mainColor = '#28a745';
        badgeClass = 'bg-success';
    } else if (successRate >= 70) {
        mainIcon = '<i class="fas fa-exclamation-triangle fa-2x"></i>';
        mainColor = '#ffc107';
        badgeClass = 'bg-warning';
    } else {
        mainIcon = '<i class="fas fa-times-circle fa-2x"></i>';
        mainColor = '#dc3545';
        badgeClass = 'bg-danger';
    }

    // Créer un ID unique pour ce rapport
    const reportId = `status-report-${Date.now()}`;

    // Construire la carte de rapport
    const $report = $(`
        <div class="card shadow-lg mb-3 border-0 fade-in" id="${reportId}">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: ${mainColor}; color: white;">
                <div class="d-flex align-items-center">
                    ${mainIcon}
                    <span class="ms-2 fw-bold">Rapport d'opérations</span>
                </div>
                <div>
                    <span class="badge ${badgeClass}">${successRate}% réussite</span>
                    <button type="button" class="btn-close btn-close-white ms-2" aria-label="Fermer"></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="status-summary p-3 d-flex justify-content-around">
                    ${statusCounts.success ? `<div class="text-center">
                        <div class="status-icon text-success"><i class="fas fa-check-circle fa-2x"></i></div>
                        <div class="status-count">${statusCounts.success}</div>
                        <div class="status-label">Réussite</div>
                    </div>` : ''}
                    ${statusCounts.warning ? `<div class="text-center">
                        <div class="status-icon text-warning"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                        <div class="status-count">${statusCounts.warning}</div>
                        <div class="status-label">Attention</div>
                    </div>` : ''}
                    ${statusCounts.error ? `<div class="text-center">
                        <div class="status-icon text-danger"><i class="fas fa-times-circle fa-2x"></i></div>
                        <div class="status-count">${statusCounts.error}</div>
                        <div class="status-label">Erreur</div>
                    </div>` : ''}
                    ${statusCounts.info ? `<div class="text-center">
                        <div class="status-icon text-info"><i class="fas fa-info-circle fa-2x"></i></div>
                        <div class="status-count">${statusCounts.info}</div>
                        <div class="status-label">Info</div>
                    </div>` : ''}
                </div>
                <hr class="m-0">
                <div class="operation-list"></div>
                <div class="card-footer bg-light p-2 text-center">
                    <button class="btn btn-sm btn-primary toggle-all-details">
                        <i class="fas fa-chevron-down me-1"></i> Afficher tous les détails
                    </button>
                </div>
            </div>
        </div>
    `);

    // Ajouter chaque opération
    const $operationList = $report.find('.operation-list');

    operationStatus.forEach((operation, index) => {
        // Formater le nom de l'opération
        const operationName = operation.operation
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());

        // Déterminer l'icône et la couleur de statut
        let statusIcon, statusColor;
        switch (operation.status) {
            case 'success':
                statusIcon = '<i class="fas fa-check-circle"></i>';
                statusColor = 'text-success';
                break;
            case 'error':
                statusIcon = '<i class="fas fa-times-circle"></i>';
                statusColor = 'text-danger';
                break;
            case 'warning':
                statusIcon = '<i class="fas fa-exclamation-triangle"></i>';
                statusColor = 'text-warning';
                break;
            default:
                statusIcon = '<i class="fas fa-info-circle"></i>';
                statusColor = 'text-info';
        }

        const detailsId = `details-${reportId}-${index}`;
        const hasDetails = operation.details && Object.keys(operation.details).length > 0;

        const $operationItem = $(`
            <div class="operation-item p-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="${statusColor} me-2">${statusIcon}</span>
                        <span class="operation-name fw-bold">${operationName}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="operation-message me-2">${operation.message}</span>
                        ${hasDetails ? `
                            <button class="btn btn-sm btn-outline-secondary toggle-details" 
                                data-target="#${detailsId}">
                                <i class="fas fa-caret-down"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
                ${hasDetails ? `
                    <div id="${detailsId}" class="operation-details mt-2 p-2" style="display: none;">
                        <div class="details-visual">
                            ${generateDetailsVisual(operation.details)}
                        </div>
                        <pre class="details-json">${JSON.stringify(operation.details, null, 2)}</pre>
                    </div>
                ` : ''}
            </div>
        `);

        $operationList.append($operationItem);
    });

    // Ajouter le rapport au dashboard
    $('#status-dashboard').prepend($report);

    // Animation d'entrée
    setTimeout(() => {
        $report.addClass('show');
    }, 100);
    // Animation d'entrée
    setTimeout(() => {
        $('#status-dashboard').remove();
    }, 3000);

    // Gérer les boutons de détails
    $report.find('.toggle-details').on('click', function () {
        const $target = $($(this).data('target'));
        $target.slideToggle(200);

        const $icon = $(this).find('i');
        if ($target.is(':visible')) {
            $icon.removeClass('fa-caret-down').addClass('fa-caret-up');
        } else {
            $icon.removeClass('fa-caret-up').addClass('fa-caret-down');
        }
    });

    // Gérer le bouton "Afficher tous les détails"
    $report.find('.toggle-all-details').on('click', function () {
        const $detailsSections = $report.find('.operation-details');
        const $allButtons = $report.find('.toggle-details i');
        const $thisButton = $(this).find('i');

        if ($detailsSections.first().is(':visible')) {
            // Masquer tous les détails
            $detailsSections.slideUp(200);
            $allButtons.removeClass('fa-caret-up').addClass('fa-caret-down');
            $thisButton.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $(this).html('<i class="fas fa-chevron-down me-1"></i> Afficher tous les détails');
        } else {
            // Afficher tous les détails
            $detailsSections.slideDown(200);
            $allButtons.removeClass('fa-caret-down').addClass('fa-caret-up');
            $thisButton.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $(this).html('<i class="fas fa-chevron-up me-1"></i> Masquer tous les détails');
        }
    });

    // Gérer le bouton de fermeture
    $report.find('.btn-close').on('click', function () {
        $report.removeClass('show');
        setTimeout(() => {
            $report.remove();
        }, 300);
    });

    // Ajouter les styles CSS
    if (!$('#operation-status-styles').length) {
        $('head').append(`
            <style id="operation-status-styles">
                #status-dashboard {
                    max-height: 80vh;
                    overflow-y: auto;
                }
                .fade-in {
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .fade-in.show {
                    opacity: 1;
                }
                .operation-item:hover {
                    background-color: rgba(0,0,0,0.03);
                }
                .operation-details {
                    background-color: #f8f9fa;
                    border-radius: 4px;
                }
                .operation-details pre {
                    background-color: rgba(0,0,0,0.05);
                    padding: 8px;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    white-space: pre-wrap;
                    margin-bottom: 0;
                }
                .status-count {
                    font-size: 1.5rem;
                    font-weight: bold;
                }
                .status-label {
                    font-size: 0.8rem;
                    text-transform: uppercase;
                }
                .details-visual {
                    margin-bottom: 10px;
                }
                .progress {
                    height: 8px;
                }
                /* Timeline pour visualisation des étapes */
                .timeline-steps {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .timeline-step {
                    position: relative;
                    text-align: center;
                    flex: 1;
                }
                .timeline-step:before {
                    content: '';
                    position: absolute;
                    top: 15px;
                    left: 0;
                    right: 0;
                    height: 3px;
                    background: #e9ecef;
                    z-index: 1;
                }
                .timeline-step:first-child:before {
                    left: 50%;
                }
                .timeline-step:last-child:before {
                    right: 50%;
                }
                .timeline-point {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background: #f8f9fa;
                    border: 3px solid #e9ecef;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    z-index: 2;
                    margin: 0 auto;
                }
                .timeline-label {
                    font-size: 0.7rem;
                    margin-top: 5px;
                }
                .timeline-point.completed {
                    background: #28a745;
                    border-color: #28a745;
                    color: white;
                }
                .timeline-point.active {
                    background: #007bff;
                    border-color: #007bff;
                    color: white;
                }
                .timeline-point.error {
                    background: #dc3545;
                    border-color: #dc3545;
                    color: white;
                }
            </style>
        `);

        // Ajouter Font Awesome si non présent
        if (!$('link[href*="font-awesome"]').length) {
            $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">');
        }
    }
}

/**
 * Génère une visualisation adaptée au type de détails fournis
 * @param {Object} details - Les détails de l'opération
 * @return {String} - HTML de la visualisation
 */
function generateDetailsVisual(details) {
    if (!details) return '';

    let visualHTML = '';

    // Si on a des étapes ou un workflow, afficher une timeline
    if (details.steps || details.workflow || details.stages) {
        const steps = details.steps || details.workflow || details.stages || [];
        if (Array.isArray(steps) && steps.length > 0) {
            visualHTML += '<div class="timeline-steps">';

            steps.forEach((step, index) => {
                let stepName = typeof step === 'string' ? step : (step.name || `Étape ${index + 1}`);
                let stepStatus = typeof step === 'object' ? (step.status || 'pending') : 'pending';

                let pointClass = '';
                let icon = '';

                switch (stepStatus) {
                    case 'completed':
                    case 'success':
                        pointClass = 'completed';
                        icon = '<i class="fas fa-check"></i>';
                        break;
                    case 'active':
                    case 'current':
                    case 'in_progress':
                        pointClass = 'active';
                        icon = '<i class="fas fa-spinner fa-spin"></i>';
                        break;
                    case 'error':
                    case 'failed':
                        pointClass = 'error';
                        icon = '<i class="fas fa-times"></i>';
                        break;
                    default:
                        icon = '<i class="fas fa-circle"></i>';
                }

                visualHTML += `
                    <div class="timeline-step">
                        <div class="timeline-point ${pointClass}">${icon}</div>
                        <div class="timeline-label">${stepName}</div>
                    </div>
                `;
            });

            visualHTML += '</div>';
        }
    }

    // Si on a des statistiques ou compteurs
    if (details.stats || details.counts || details.progress) {
        const stats = details.stats || details.counts || {};
        const progress = details.progress || {};

        // Afficher une barre de progression si disponible
        if (typeof progress === 'number' || progress.value) {
            const progressValue = typeof progress === 'number' ? progress : (progress.value || 0);
            const progressMax = progress.max || 100;
            const percentage = Math.round((progressValue / progressMax) * 100);

            let progressClass = 'bg-info';
            if (percentage >= 100) progressClass = 'bg-success';
            else if (percentage >= 70) progressClass = 'bg-primary';
            else if (percentage >= 30) progressClass = 'bg-warning';
            else if (percentage < 30) progressClass = 'bg-danger';

            visualHTML += `
                <div class="progress-container mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small>${progress.label || 'Progression'}</small>
                        <small>${percentage}%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar ${progressClass}" role="progressbar" 
                             style="width: ${percentage}%" aria-valuenow="${percentage}" 
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            `;
        }

        // Afficher des compteurs
        if (Object.keys(stats).length > 0) {
            visualHTML += '<div class="row stats-container g-2 mb-2">';

            Object.keys(stats).forEach(key => {
                const value = stats[key];
                let icon = 'fa-chart-bar';

                // Déterminer l'icône selon le nom du compteur
                if (key.includes('success') || key.includes('réussi')) icon = 'fa-check-circle';
                else if (key.includes('error') || key.includes('erreur')) icon = 'fa-times-circle';
                else if (key.includes('warning') || key.includes('avertissement')) icon = 'fa-exclamation-triangle';
                else if (key.includes('info')) icon = 'fa-info-circle';
                else if (key.includes('total')) icon = 'fa-calculator';
                else if (key.includes('time') || key.includes('temps')) icon = 'fa-clock';
                else if (key.includes('file') || key.includes('fichier')) icon = 'fa-file';
                else if (key.includes('user') || key.includes('utilisateur')) icon = 'fa-user';

                const statName = key
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());

                visualHTML += `
                    <div class="col-6">
                        <div class="p-2 border rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas ${icon} me-2"></i>
                                <div>
                                    <div class="small text-muted">${statName}</div>
                                    <div class="fw-bold">${value}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            visualHTML += '</div>';
        }
    }

    return visualHTML;
}



// Fonction pour afficher les toasts de notification (à implémenter selon votre système)
function showToaster(message, type = 'warning') {
    // Si vous utilisez Bootstrap Toast
    $('.toast-container').remove(); // Supprimer les toasts existants

    const toastContainer = $('<div>').addClass('toast-container position-fixed bottom-0 end-0 p-3');
    const toast = $('<div>')
        .addClass(`toast bg-${type} text-white`)
        .attr({
            'role': 'alert',
            'aria-live': 'assertive',
            'aria-atomic': 'true'
        });

    const toastHeader = $('<div>')
        .addClass('toast-header bg-transparent text-white border-0')
        .append(
            $('<strong>').addClass('me-auto').text('Notification'),
            $('<button>').addClass('btn-close btn-close-white').attr({
                'type': 'button',
                'data-bs-dismiss': 'toast',
                'aria-label': 'Close'
            })
        );

    const toastBody = $('<div>').addClass('toast-body').text(message);

    toast.append(toastHeader, toastBody);
    toastContainer.append(toast);
    $('body').append(toastContainer);

    const bsToast = new bootstrap.Toast(toast[0], {
        autohide: true,
        delay: 3000
    });
    bsToast.show();
}