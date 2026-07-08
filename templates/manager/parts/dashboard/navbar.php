<div class="container-fluid">
  <div class="row">
    <!-- Navbar latérale pour grands écrans -->
    <div class="sidebar-container col-lg-3 col-xl-2 d-none d-lg-block p-0">
      <div class="sidebar" style="overflow-y: auto;">
        <!-- Logo et titre -->
        <div class="sidebar-header">
          <a href="/dashboard" class="sidebar-brand">
            <img src="/assets/images/logo.svg" alt="NM">
            <span>Dashboard</span>
          </a>
        </div>
        <!-- Menu principal -->
        <div class="sidebar-menu">
          <!-- Profil -->
          <div class="menu-item">
            <a class="menu-link" data-bs-toggle="collapse" href="#profileSubmenu" role="button" aria-expanded="false">
              <div class="menu-icon">
                <i class="fa fa-user"></i>
              </div>
              <span>Profil</span>
              <i class="fa fa-chevron-down menu-arrow"></i>
            </a>
            <div class="collapse submenu noThis" id="profileSubmenu">
              <a class="submenu-item" data-bs-toggle="modal" data-bs-target="#editProfileModal" id="editProfileModalTrig" data-id="<?= $_SESSION['user_id'] ?>" href="#">
                <i class="fa-solid fa-gear"></i>
                <span>Gérer</span>
              </a>
              <a class="submenu-item" href="/logout">
                <i class="fa fa-sign-out-alt"></i>
                <span>Déconnexion</span>
              </a>
            </div>
          </div>

          <!-- Accueil -->
          <div class="menu-item">
            <a class="menu-link" href="/" target="_blank">
              <div class="menu-icon">
                <i class="fa fa-home"></i>
              </div>
              <span>Page d'accueil</span>
            </a>
          </div>
          <hr style="margin: 0; padding: 0;">

          <?php if ($_SESSION['user_role'] === 'admin'): ?>

            <!-- Managers -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#managersSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa fa-users"></i>
                </div>
                <span>Managers</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="managersSubmenu">
                <a class="submenu-item" data-bs-toggle="modal" data-bs-target="#addManagerModal" href="#">
                  <i class="fa fa-user-plus"></i>
                  <span>Nouveau</span>
                </a>
                <a class="submenu-item" data-bs-toggle="modal" data-bs-target="#manageManagersModal" href="#">
                  <i class="fa fa-cog"></i>
                  <span>Gérer</span>
                </a>
              </div>
            </div>

            <!-- Pages -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#pageSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa-solid fa-pen-nib"></i>
                </div>
                <span>Pages</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="pageSubmenu">
                <a class="submenu-item" data-bs-toggle="collapse" href="#homeManage" role="button">
                  <i class="fa fa-home"></i>
                  <span>Accueil</span>
                </a>
                <a class="submenu-item" data-bs-toggle="collapse" href="#shopAreaManage" role="button">
                  <i class="fa-solid fa-shop"></i>
                  <span>Boutique</span>
                </a>
                <a class="submenu-item" data-bs-toggle="collapse" href="#contactAreaManage" role="button">
                  <i class="fa-solid fa-address-book"></i>
                  <span>Contact</span>
                </a>
                <a class="submenu-item" data-bs-toggle="collapse" href="#articlesAreaManage" role="button">
                  <i class="fa-solid fa-pen"></i>
                  <span>Articles</span>
                </a>
                <a class="submenu-item" id="sectionsManageTrigger" data-page="home" data-bs-toggle="collapse" href="#sectionsManage" role="button">
                  <i class="fas fa-folder"></i>
                  <span>Sections</span>
                </a>
              </div>
            </div>

            <!-- Site -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#siteSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa-solid fa-globe"></i>
                </div>
                <span>Site</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="siteSubmenu">
                <a class="submenu-item" data-bs-toggle="modal" data-bs-target="#logModal" href="#logForm" role="button">
                  <i class="fa-solid fa-file"></i>
                  <span>Log</span>
                </a>
                <a class="submenu-item" href="/dashboard/database" target="_blank">
                  <i class="fa-solid fa-database"></i>
                  <span>Database</span>
                </a>
                <a class="submenu-item" href="/dashboard/documentation" target="_blank">
                  <i class="fa-solid fa-book"></i>
                  <span>Documentation</span>
                </a>
              </div>
            </div>
            <hr style="margin: 0; padding: 0;">
          <?php endif; ?>


          <!-- Produits -->
          <div class="menu-item">
            <a class="menu-link" data-bs-toggle="collapse" href="#produitsSubmenu" role="button" aria-expanded="false">
              <div class="menu-icon">
                <i class="fas fa-box"></i>
              </div>
              <span>Produits</span>
              <i class="fa fa-chevron-down menu-arrow"></i>
            </a>
            <div class="collapse submenu noThis" id="produitsSubmenu">
              <a class="submenu-item" href="#" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="fa fa-cog"></i>
                <span>Gérer</span>
              </a>
              <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin')): ?>
                <a class="submenu-item" id="catModalTrig" href="#" data-bs-toggle="modal" data-bs-target="#mangeCategoriesModal">
                  <i class="fa-solid fa-layer-group"></i>
                  <span>Catégories</span>
                </a>
                <a class="submenu-item" href="#" data-bs-toggle="modal" data-bs-target="#observeStockModal">
                  <i class="fa-solid fa-boxes-stacked"></i>
                  <span>Stock</span>
                </a>
              <?php endif; ?>
            </div>
          </div>

          <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin')): ?>
            <!-- Commandes -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#commandesSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <span>Commandes</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="commandesSubmenu">
                <a class="submenu-item" id="ordersListModalTrigger" data-bs-toggle="modal" href="#ordersModal" role="button">
                  <i class="fa fa-cog"></i>
                  <span>Gérer</span>
                </a>
                <a class="submenu-item" id="orderStatsTrigger2" data-bs-toggle="modal" href="#order2sStatsModal" role="button">
                  <i class="fa fa-chart-bar"></i>
                  <span>Statistiques</span>
                </a>
              </div>
            </div>

            <!-- Feedback -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#feedbackSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa fa-envelope"></i>
                </div>
                <span>Feedback</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="feedbackSubmenu">
                <a class="submenu-item" href="#" id="subscriberModalTrigger" data-bs-toggle="modal" data-bs-target="#subscriberModal">
                  <i class="fa fa-users"></i>
                  <span>Abonnés</span>
                </a>
                <a class="submenu-item" href="#" id="feedbacksListModalTrigger" data-bs-toggle="modal" data-bs-target="#feedbacksModal">
                  <i class="fas fa-comment-alt"></i>
                  <span>Contacts</span>
                </a>
              </div>
            </div>
            <hr style="margin: 0; padding: 0;">

            <!-- Articles -->
            <div class="menu-item">
              <a class="menu-link" data-bs-toggle="collapse" href="#arttSubmenu" role="button" aria-expanded="false">
                <div class="menu-icon">
                  <i class="fa-solid fa-pencil"></i>
                </div>
                <span>Articles</span>
                <i class="fa fa-chevron-down menu-arrow"></i>
              </a>
              <div class="collapse submenu noThis" id="arttSubmenu">
                <a class="submenu-item articles-manage" data-bs-toggle="collapse" href="#newArticle" role="button">
                  <i class="fa-solid fa-gear"></i>
                  <span>Gérer</span>
                </a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Navbar horizontale pour petits écrans -->
    <div class="col-12 d-lg-none">
      <nav class="navbar navbar-expand-lg bg-body-tertiary rounded" aria-label="Eleventh navbar example">
        <div class="container-fluid">
          <span class="navbar-brand">dashboard</span>
          <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample09" aria-controls="navbarsExample09" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="navbar-collapse collapse" id="navbarsExample09">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link" href="/" target="_blank">
                  <i class="fa fa-home" aria-hidden="true"></i> Page d'acceuil
                </a>
              </li>

              <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-users" aria-hidden="true"></i> Managers
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="modal" data-bs-target="#addManagerModal" href="#">
                        <i class="fa fa-user-plus" aria-hidden="true"></i> Nouveau
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="modal" data-bs-target="#manageManagersModal" href="#">
                        <i class="fa fa-cog" aria-hidden="true"></i> Gérer
                      </a>
                    </li>
                  </ul>
                </li>

                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-pen-nib"></i> Pages
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="collapse" href="#homeManage" role="button">
                        <i class="fa fa-home" aria-hidden="true"></i> Acceuil
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="collapse" href="#shopAreaManage" role="button">
                        <i class="fa-solid fa-shop"></i> Boutique
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="collapse" href="#contactAreaManage" role="button">
                        <i class="fa-solid fa-address-book"></i> Contact
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="collapse" href="#articlesAreaManage" role="button">
                        <i class="fa-solid fa-pen"></i> Articles
                      </a>
                    </li>
                    <li class="dropdown-item mainCTrig">
                      <a class="nav-link py-1" id="sectionsManageTrigger" data-page="home" data-bs-toggle="collapse" href="#sectionsManage" role="button">
                        <i class="fas fa-folder"></i> Sections
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-globe"></i> Site
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="/dashboard/database" target="_blank" role="button">
                        <i class="fa-solid fa-database"></i> Database
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" data-bs-toggle="modal" data-bs-target="#logModal" href="#logForm" role="button">
                        <i class="fa-solid fa-file"></i> Log
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" data-bs-toggle="collapse" href="/dashboard/documentation" target="_blank" role="button">
                        Documentation
                      </a>
                    </li>
                  </ul>
                </li>

              <?php endif; ?>

              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-box"></i> Produits
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item mainCTrig" href="#" data-bs-toggle="modal" data-bs-target="#productModal">
                      <i class="fa fa-cog" aria-hidden="true"></i> Gérer
                    </a>
                  </li>
                  <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin')): ?>
                    <li>
                      <a class="dropdown-item mainCTrig" id="catModalTrig" href="#" data-bs-toggle="modal" data-bs-target="#mangeCategoriesModal">
                        <i class="fa-solid fa-layer-group"></i> Categories
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" id="observeStockBtn" href="#" data-bs-toggle="modal" data-bs-target="#observeStockModal">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        Stock
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>

              <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin')): ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-pencil"></i>Articles
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item mainCTrig articles-manage" data-bs-toggle="collapse" href="#newArticle" role="button">
                        <i class="fa-solid fa-gear"></i> Gérer
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-basket-shopping"></i> Commandes
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item mainCTrig" id="ordersListModalTriggerMobile" data-bs-toggle="modal" href="#ordersModal" role="button">
                        <i class="fa fa-cog" aria-hidden="true"></i> Gérer
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" id="orderStatsTrigger2" data-bs-toggle="modal" href="#order2sStatsModal" role="button">
                        <i class="fa fa-chart-bar" aria-hidden="true"></i> Statistiques
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-envelope" aria-hidden="true"></i> Feedback
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item mainCTrig" href="#" id="subscriberModalTriggerMobile" data-bs-toggle="modal" data-bs-target="#subscriberModal">
                        <i class="fa fa-users" aria-hidden="true"></i> Abonnés
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item mainCTrig" href="#" id="feedbacksListModalTriggerMobile" data-bs-toggle="modal" data-bs-target="#feedbacksModal">
                        <i class="fas fa-comment-alt" aria-hidden="true"></i> Contacts
                      </a>
                    </li>
                  </ul>
                </li>
              <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-user-circle" aria-hidden="true"></i> Profile
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item mainCTrig" href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal" id="editProfileModalTrig" data-id="<?= $_SESSION['user_id'] ?>">
                      <i class="fa-solid fa-gear"></i> Gérer
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item mainCTrig" href="/logout">
                      <i class="fa fa-sign-out-alt" aria-hidden="true"></i> Logout
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>