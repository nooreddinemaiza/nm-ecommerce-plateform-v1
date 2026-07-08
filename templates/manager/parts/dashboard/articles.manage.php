<div class="card collapse" id="newArticle" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="sectionsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="list-article-tab" data-bs-toggle="tab" href="#list-articles" role="tab" aria-selected="false">
                    Liste
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="add-article-tab" data-bs-toggle="tab" href="#add-article" role="tab" aria-selected="false">
                    Nouveu
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="articlesTabContent">
            <div class="tab-pane fade" id="add-article" role="tabpanel" aria-labelledby="custom-section-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient">
                        <h5 class="card-title mb-0">Ajouter un nouvel article</h5>
                    </div>
                    <!-- Liste des sections existantes -->
                    <div class="card-body" style="max-height: 70vh; overflow-y:auto">
                        <form id="newArticleForm">

                            <div class="mb-3">
                                <label for="afficher" class="form-label">Publié</label>
                                <input type="checkbox" name="afficher" id="afficher" checked />
                                <small>(Marquer pour indiquer que l'article est Publié ou non)</small>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre</label>
                                <input type="text" name="title" id="title" class="form-control man-title">
                            </div>

                            <div class="mb-3 man-slug">
                                <label for="slug" class="form-label">Slug (URL)</label>
                                <input type="text" name="slug" id="slug" class="form-control man-slug-input">
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Image à la une</label>
                                <input type="file" name="image" id="image" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Contenu</label>
                                <textarea name="content" id="content" class="form-control" rows="10"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Extrait</label><small> (<span id="extwc">200</span> mots max)</small>
                                <textarea name="excerpt" id="excerpt" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="row mb-3">
                                <h4 class="mt-3 mb-3">Méta données</h4>
                                <div class="col col-12 mt-3">
                                    <label for="article_meta_descp">Description</label>
                                    <textarea type="text" name="article_meta_descp" id="article_meta_descp" class="form-control" placeholder="Meta description"></textarea>
                                </div>
                                <div class="col col-12 mt-3">
                                    <label for="article_meta_tags">Tags</label>
                                    <div class="form-text">
                                        <div class=" text-muted alert alert-info">
                                            <small>
                                                <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                                <br>
                                                Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                            </small>
                                        </div>
                                        <textarea class="form-control meta_tags_input" type="text" name="article_meta_tags" id="article_meta_tags" placeholder="Meta Tags"></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Modal de modification d'article -->
            <div class="modal fade" id="editArticleModal" tabindex="-1" aria-labelledby="editArticleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-gradient">
                            <h5 class="modal-title" id="editArticleModalLabel">Modifier l'article</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y:auto">
                            <div id="editArticleLoading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2">Chargement de l'article...</p>
                            </div>

                            <form id="editArticleForm" style="display: none;">
                                <div class="mb-3">
                                    <label for="edit_afficher" class="form-label">Publier ?</label>
                                    <input type="checkbox" name="afficher" id="edit_afficher">
                                    <small>(Marquer pour indiquer que l'article est publié ou non)</small>
                                </div>
                                <input type="hidden" name="article_id" id="edit_article_id">

                                <div class="mb-3">
                                    <label for="edit_title" class="form-label man-title">Titre</label>
                                    <input type="text" name="title" id="edit_title" class="form-control">
                                </div>

                                <div class="mb-3 man-slug">
                                    <label for="edit_slug" class="form-label">Slug (URL)</label>
                                    <input type="text" name="slug" id="edit_slug" class="form-control man-slug-input">
                                </div>

                                <div class="mb-3">
                                    <label for="edit_image" class="form-label">Image à la une</label>
                                    <div class="d-flex align-items-center mb-2">
                                        <img id="current_image_preview" src="" alt="Image actuelle" class="img-thumbnail me-3" style="max-height: 100px; display: none;">
                                        <div>
                                            <span id="current_image_name" class="d-block mb-1"></span>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="keep_current_image" name="keep_current_image" checked>
                                                <label class="form-check-label" for="keep_current_image">
                                                    Conserver l'image actuelle
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" name="image" id="edit_image" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_content" class="form-label">Contenu</label>
                                    <textarea name="content" id="edit_content" class="form-control editor" rows="10"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_excerpt" class="form-label">Extrait</label><small> (<span id="edit_extwc">200</span> mots max)</small>
                                    <textarea name="excerpt" id="edit_excerpt" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row mb-3">
                                    <h4 class="mt-3 mb-3">Méta données</h4>
                                    <div class="col col-12 mt-3">
                                        <label for="earticle_meta_descp">Description</label>
                                        <textarea type="text" name="earticle_meta_descp" id="earticle_meta_descp" class="form-control" placeholder="Meta description"></textarea>
                                    </div>
                                    <div class="col col-12 mt-3">
                                        <label for="earticle_meta_tags">Tags</label>
                                        <div class="form-text">
                                            <div class=" text-muted alert alert-info">
                                                <small>
                                                    <strong>Tags</strong> : Ajoutez des mots-clés pertinents pour améliorer le référencement de votre produit.
                                                    <br>
                                                    Tapez un mot ou une expression, puis appuyez sur <strong>Entrée</strong> ou tapez une <strong>virgule</strong> pour valider chaque tag. Ne pressez pas <strong>Espace</strong> à l’intérieur d’un tag.
                                                </small>
                                            </div>
                                            <textarea class="form-control meta_tags_input" type="text" name="earticle_meta_tags" id="earticle_meta_tags" placeholder="Meta Tags"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="saveArticleChanges">Enregistrer les modifications</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane active" id="list-articles" role="tabpanel" aria-labelledby="articles-section-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient">
                        <h5 class="card-title mb-0">Liste des articles <a href="/actualites" target="_blank"> Visiter <i class="fa fa-link" aria-hidden="true"></i></a></h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and filter controls -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="article-search" class="form-control" placeholder="Rechercher...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="article-filter" class="form-select">
                                    <option value="all">Tous les articles</option>
                                    <option value="published">Publiés</option>
                                    <option value="unpublished">Non publiés</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="article-sort" class="form-select">
                                    <option value="id-asc">ID (croissant)</option>
                                    <option value="id-desc">ID (décroissant)</option>
                                    <option value="title-asc">Titre (A-Z)</option>
                                    <option value="title-desc">Titre (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        <!-- Results count -->
                        <div class="d-flex justify-content-between mb-2">
                            <span id="article-count" class="text-muted">0 articles trouvés</span>
                            <button id="refresh-articles" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                        <!-- Articles table -->
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table id="articles-table" class="table table-striped table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Visites</th>
                                        <th>Créateur</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="articles-table-body">
                                    <!-- Articles will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#newArticle">Fermer</button>
    </div>
</div>