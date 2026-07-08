<!-- ***** Header Area Start ***** -->
<header class="header-area header-sticky">
  <div class="container" id="page-header">
    <div class="row">
      <div class="col-12">
        <nav class="main-nav">
          <!-- ***** Logo Start ***** -->
          <a href="/" class="logo" style="max-width: 100px;
  max-height: 100px;
  margin-top: 15px;
}">
            <img src="<?= WEB_LOGO_URL ?>" alt="" style="height: 100%;">
          </a>
          <!-- ***** Logo End ***** -->
          <!-- ***** Menu Start ***** -->
          <ul class="nav">
            <li><a href="/" class="<?= ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/home' || $_SERVER['REQUEST_URI'] === '') ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="/shop" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/shop') ? 'active' : '' ?>">Boutique</a></li>
            <li><a href="/contact" class="<?= $_SERVER['REQUEST_URI'] === '/contact' ? 'active' : '' ?>">Contactez-nous</a></li>
            <li><a href="/about" class="<?= $_SERVER['REQUEST_URI'] === '/about' ? 'active' : '' ?>">À propos</a></li>
            <li><a href="/devis" class="<?= $_SERVER['REQUEST_URI'] === '/devis' ? 'active' : '' ?>">Devis</a></li>

            <?php
            if (!empty($_SESSION['user_id'])):
            ?>
              <li>
                <a href="/dashboard" target="_blank" title="Tableau de bord">
                  <i class="fa-solid fa-gauge"></i>
                </a>
              </li>
            <?php
            endif
            ?>
          </ul>
          <style>
            #panier-button:hover {
              background: #ee626b !important;
              color: white;
            }

            #panier-button {
              width: 45px;
              height: 45px;
              position: fixed;
              right: 10px;
              top: 85px;
              padding-top: 12px;
              text-align: center;
              background: white;
              border-radius: 25px;
            }

            #panier-button span {
              position: absolute;
              background: none !important;
              color: red;
              left: 28%;
              top: 3px;
            }

            @keyframes shake {

              0%,
              100% {
                transform: translateX(0);
              }

              25% {
                transform: translateX(-3px);
              }

              50% {
                transform: translateX(3px);
              }

              75% {
                transform: translateX(-3px);
              }
            }

            .shake {
              animation: shake 0.3s ease-in-out;
            }
          </style>
          <a class='menu-trigger' id="menu-trigger">
            <span>Menu</span></a>
          <a href="#!" id="panier-button">
            <i class="fas fa-shopping-cart fa-lg"></i>
            <span class="badge badge-pill bg-danger d-none" id="cart-items-n"></span>
          </a>
          <!-- ***** Menu End ***** -->
        </nav>
      </div>
    </div>
    <?php
    include_once('cart.php');
    ?>
  </div>
</header>
<div id="alerts-place" style="position: fixed; top: 120px; max-width: 650px; z-index: 2002; left: 40%;"></div>