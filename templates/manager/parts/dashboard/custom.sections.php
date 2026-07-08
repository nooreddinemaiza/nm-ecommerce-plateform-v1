<div class="card collapse" id="sectionsManage" data-bs-parent="#collapseGroup">
    <div class="card-header">
        <!-- <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#homeManage" aria-label="Close"></button> -->
        <ul class="nav nav-tabs card-header-tabs" id="sectionsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link custom-sections-tab active" id="homeS-tab" data-bs-toggle="tab" href="#home-custom-sections" role="tab" data-page="home" aria-controls="personnelized-sections" aria-selected="true">Acceuil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-sections-tab" id="shopS-tab" data-bs-toggle="tab" href="#shop-custom-sections" role="tab" data-page="shop" aria-controls="personnelized-sections" aria-selected="true">Boutique</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="sectionsTabContent">
            <div class="alert alert-info alert-dismissible fade show notify-sect" role="alert">
                La section <strong>Footer</strong> de la page d'acceuil s'affiche dans toutes les pages
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <!-- Custom sections part -->
            <div class="tab-pane fade show active" id="home-custom-sections" role="tabpanel" aria-labelledby="custom-section-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient">
                        <h5 class="card-title mb-0">Section Personnalisée</h5>
                    </div>
                    <!-- Liste des sections existantes -->
                    <div class="card-body">
                        <h6>Sections existantes :</h6>
                        <ul id="home-sections-list" class="list-group" style="max-height: 280px;overflow-y: auto;">
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Custom sections part -->
            <div class="tab-pane fade" id="shop-custom-sections" role="tabpanel" aria-labelledby="custom-section-tab">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient">
                        <h5 class="card-title mb-0">Section Personnalisée</h5>
                    </div>
                    <!-- Liste des sections existantes -->
                    <div class="card-body">
                        <h6>Sections existantes :</h6>
                        <ul id="shop-sections-list" class="list-group" style="max-height: 280px;overflow-y: auto;">
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Ajouter une nouvelle section -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary" id="add-sections-btn" data-bs-toggle="modal" data-bs-target="#SectionsTempleModal">
                    <i class="fas fa-plus me-2"></i>Ajouter une section
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#sectionsManage">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
            </div>
            <!-- Modal de sélection de templates -->
            <div class="modal fade" id="SectionsTempleModal" tabindex="-1" aria-labelledby="SectionsTempleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="addSectionsFormContent" class="modal-content">
                        <div class="modal-header bg-gradient text-white">
                            <h5 class="modal-title" id="SectionsTempleModalLabel">Ajouter une Section</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body" style="height: 70vh;">
                            <div class="row" style="height: 100%;">
                                <div class="col col-4">
                                    <div class="row mt-3">
                                        <button type="button" class="btn btn-outline-primary secTempBtn" data-page="home" data-id="1" data-toggle="button" aria-pressed="false" autocomplete="off">
                                            Modèle Liste
                                        </button>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="button" class="btn btn-outline-primary secTempBtn" data-page="home" data-id="2" data-toggle="button" aria-pressed="false" autocomplete="off">
                                            Modèle Annonce
                                        </button>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="button" class="btn btn-outline-primary secTempBtn" data-page="home" data-id="3" data-toggle="button" aria-pressed="false" autocomplete="off">
                                            Modéle Sections
                                        </button>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="button" class="btn btn-outline-primary secTempBtn" data-page="home" data-id="4" data-toggle="button" aria-pressed="false" autocomplete="off">
                                            Modéle Galerie
                                        </button>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="button" class="btn btn-outline-primary secTempBtn" data-page="home" data-id="5" data-toggle="button" aria-pressed="false" autocomplete="off">
                                            Modéle Vide
                                        </button>
                                    </div>
                                </div>
                                <div class="col col-8" id="secTempViewer">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="sectionEditModal1" tabindex="-1" aria-labelledby="sectionEditModal1Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form id="sectionEditForm1" class="modal-content">
                        <div class="modal-header bg-gradient text-white">
                            <h5 class="modal-title" id="sectionEditModal1Label">Ajout de section - Modèle 1</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="alert alert-info">
                                <strong>Contraintes :</strong>
                                <ul>
                                    <li>2 à 4 listes maximum</li>
                                    <li>2 à 5 éléments par liste</li>
                                    <li>Les textes ne peuvent pas dépasser 50 caractères</li>
                                    <li>Chaque élément peut être un texte simple ou un lien</li>
                                </ul>
                            </div>

                            <div id="listsContainer">
                                <!-- Les listes seront générées ici par JavaScript -->
                            </div>

                            <button type="button" class="btn btn-success add-list mt-3">
                                <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                Ajouter une liste
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="sectionEditModal2" tabindex="-1" aria-labelledby="sectionEditModal2Label" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="sectionEditForm2" class="modal-content">
                        <div class="modal-header bg-gradient text-white">
                            <h5 class="modal-title" id="sectionEditModal2Label">Éditer la section - Modèle Contenu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                Ce modèle comprend un petit titre, un grand titre, une description, et optionnellement une image et un lien.
                            </div>

                            <div class="mb-3">
                                <label for="model2SmallTitle" class="form-label">Petit titre</label>
                                <input type="text" class="form-control" id="model2SmallTitle" required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="model2LargeTitle" class="form-label">Grand titre</label>
                                <input type="text" class="form-control" id="model2LargeTitle" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="model2Description" class="form-label">Description</label>
                                <textarea class="form-control" id="model2Description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="model2ImageUrl" class="form-label">URL de l'image (optionnelle)</label>
                                <input type="url" class="form-control" id="model2ImageUrl">
                                <div class="form-text">Laissez vide si vous ne souhaitez pas inclure d'image.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lien (optionnel)</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="url" class="form-control" id="model2LinkUrl" placeholder="URL du lien">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="model2LinkText" placeholder="Texte du lien" maxlength="50">
                                    </div>
                                </div>
                                <div class="form-text">Laissez vide si vous ne souhaitez pas inclure de lien.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="sectionEditModal3" tabindex="-1" aria-labelledby="sectionEditModal3Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sectionEditModal3Label">Édition de la section Features</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="sectionEditForm3">
                                <div class="mb-3">
                                    <label for="model3Title" class="form-label">Titre principal</label>
                                    <input type="text" class="form-control" id="model3Title" placeholder="Titre de la section" maxlength="100" required>
                                </div>

                                <h5 class="mb-3">Éléments</h5>
                                <div id="featuresContainer">
                                    <!-- Les éléments seront ajoutés ici dynamiquement -->
                                </div>

                                <button type="button" class="btn btn-primary add-feature" onclick="addFeature()">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                    Ajouter un élément
                                </button>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="sectionEditModal4" tabindex="-1" aria-labelledby="sectionEditModal4Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sectionEditModal4Label">Édition de la galerie d'images</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="sectionEditForm4">
                                <div class="mb-3">
                                    <label for="model4Title" class="form-label">Titre de la galerie</label>
                                    <input type="text" class="form-control" id="model4Title" placeholder="Titre de la galerie" maxlength="100" required>
                                </div>

                                <div class="mb-3">
                                    <label for="model4Description" class="form-label">Description</label>
                                    <textarea class="form-control" id="model4Description" rows="3" maxlength="300"></textarea>
                                </div>

                                <h5 class="mb-3">Images</h5>
                                <div id="imagesContainer">
                                    <!-- Les images seront ajoutées ici dynamiquement -->
                                </div>

                                <button type="button" class="btn btn-primary add-image">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                    Ajouter une image
                                </button>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="sectionEditModal5" tabindex="-1" aria-labelledby="sectionEditModal5Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sectionEditModal5Label">Model vide</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="sectionEditForm5">
                                <div>
                                    Enregistrer un model vide
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates pour les vues (inchangés) -->
            <template class="secTempView" data-id="1">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3">
                            <ul class="list-group">
                                <li class="list-group-item fw-bold text-uppercase bg-light text-dark">Titre de la liste</li>
                                <li class="list-group-item">Texte 1</li>
                                <li class="list-group-item">Texte 2</li>
                                <li class="list-group-item">Texte 3</li>
                            </ul>
                        </div>
                        <div class="col-lg-3">
                            <ul class="list-group">
                                <li class="list-group-item fw-bold text-uppercase bg-light text-dark">Titre de la liste</li>
                                <li class="list-group-item">Texte 1</li>
                                <li class="list-group-item">Texte 2</li>
                                <li class="list-group-item">Texte 3</li>
                            </ul>
                        </div>
                        <div class="col-lg-3">
                            <ul class="list-group">
                                <li class="list-group-item fw-bold text-uppercase bg-light text-dark">Titre de la liste</li>
                                <li class="list-group-item">Texte 1</li>
                                <li class="list-group-item">Texte 2</li>
                                <li class="list-group-item">Texte 3</li>
                            </ul>
                        </div>
                        <div class="col-lg-3">
                            <ul class="list-group">
                                <li class="list-group-item fw-bold text-uppercase bg-light text-dark">Titre de la liste</li>
                                <li class="list-group-item">Texte 1</li>
                                <li class="list-group-item">Texte 2</li>
                                <li class="list-group-item">Texte 3</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </template>
            <template class="secTempView" data-id="2">
                <div class="container">
                    <div class="row">
                        <div class="shop">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="section-heading">
                                        <h6>Titre 1</h6>
                                        <h2>Titre 2</h2>
                                    </div>
                                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolor similique</p>
                                    <div class="main-button">
                                        <a href="#">Texte lien</a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <img src="/assets/images/product-image/No_Image_Available.jpg" alt="" class="img-fluid h-100 w-100" style="object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template class="secTempView" data-id="3">
                <div class="container">
                    <div class="row">
                        <h5 class="text-center mb-4">Nos services</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6>Service 1</h6>
                                        <p class="small">Description courte du premier service offert.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-star fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6>Service 2</h6>
                                        <p class="small">Description courte du deuxième service offert.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-cog fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6>Service 3</h6>
                                        <p class="small">Description courte du troisième service offert.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-heart fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6>Service 4</h6>
                                        <p class="small">Description courte du quatrième service offert.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template class="secTempView" data-id="4">
                <div class="container">
                    <div class="row">
                        <h5 class="text-center mb-3">Titre de la galerie</h5>
                        <p class="text-center mb-4">Description de la galerie d'images.</p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="bg-light p-3" style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image fa-3x text-secondary"></i>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text small text-center">Légende de l'image 1</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="bg-light p-3" style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image fa-3x text-secondary"></i>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text small text-center">Légende de l'image 2</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="bg-light p-3" style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image fa-3x text-secondary"></i>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text small text-center">Légende de l'image 3</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template class="secTempView" data-id="5">
                <div class="container">
                    <div class="row">
                        <h5 class="text-center mb-3">Modéle vide</h5>
                        <p class="text-center mb-4">Ce modéle vous permet de bien organiser vos sections personnalisées</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>