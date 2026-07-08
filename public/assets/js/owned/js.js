
$(document).ready(function () {
  $('#subscribeButton').on('click', function () {
    addSubscriber();
  });
  $('#subscriberEmail').on("keyup", function (event) {
    if (event.key === 'Enter') {
      addSubscriber();
    }
  });
  // Menu Dropdown Toggle
  $("#menu-trigger").on('click', function () {
    $("#menu-trigger").toggleClass('active');
    $('.header-area .nav').slideToggle(200);
  });
  $(".toggle-password").on("click", function () {
    // Cible le champ d'entrée lié via l'attribut data-target
    const inputId = $(this).data("target");
    const input = $("#" + inputId);
    const icon = $(this).find("i");

    // Bascule le type entre 'password' et 'text'
    if (input.attr("type") === "password") {
      input.attr("type", "text");
      icon.removeClass("fa-solid fa-eye").addClass("fa-solid fa-eye-slash");
    } else {
      input.attr("type", "password");
      icon.removeClass("fa-solid fa-eye-slash").addClass("fa-solid fa-eye");
    }
  });
  // Afficher ou masquer le panier
  window.toggleCart = function () {
    $('#cart-popup').toggle();
  };
  $('[data-toggle="tooltip"]').tooltip();
  $('[data-bs-toggle="tooltip"]').on('shown.bs.tooltip', function () {
    const $tooltip = $(this);
    setTimeout(function () {
      $tooltip.tooltip('hide');
    }, 2000); // 2000ms = 2 secondes
  });

  // Sélectionne toutes les images dans les cartes de la galerie
  $('.gallery-card .img-container').each(function () {
    // Ajoute un bouton de zoom dans le coin de chaque image
    $(this).append('<div class="zoom-button"><i class="fas fa-search-plus"></i></div>');

    // Cache le bouton de zoom par défaut
    $(this).find('.zoom-button').hide();
  });

  // Affiche le bouton de zoom au survol de l'image
  $('.gallery-card .img-container').hover(
    function () {
      // Apparition du bouton avec une animation
      $(this).find('.zoom-button').fadeIn(200);
    },
    function () {
      // Disparition du bouton lorsque la souris quitte l'image
      $(this).find('.zoom-button').fadeOut(200);
    }
  );

  // Gestion du clic sur le bouton de zoom
  $(document).on('click', '.zoom-button', function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Récupère l'URL de l'image
    var imgSrc = $(this).siblings('img').attr('src');
    var imgAlt = $(this).siblings('img').attr('alt') || 'Image';

    // Crée une modal pour afficher l'image en plein écran
    $('body').append(`
        <div class="zoom-modal">
          <div class="zoom-modal-content">
            <span class="zoom-close">&times;</span>
            <img src="${imgSrc}" alt="${imgAlt}" class="zoom-img">
            <div class="zoom-caption">${imgAlt}</div>
          </div>
        </div>
      `);

    // Affiche la modal avec une animation
    $('.zoom-modal').fadeIn(300);

    // Empêche le défilement du contenu en arrière-plan
    $('body').addClass('no-scroll');
  });

  // Ferme la modal au clic sur le bouton de fermeture ou en dehors de l'image
  $(document).on('click', '.zoom-close, .zoom-modal', function (e) {
    if ($(e.target).is('.zoom-modal') || $(e.target).is('.zoom-close')) {
      $('.zoom-modal').fadeOut(300, function () {
        $(this).remove();
        $('body').removeClass('no-scroll');
      });
    }
  });

  // Empêche la fermeture de la modal lorsqu'on clique sur l'image
  $(document).on('click', '.zoom-modal-content', function (e) {
    e.stopPropagation();
  });
  // Créer l'élément tooltip
  const tooltip = document.createElement('div');
  tooltip.className = 'custom-tooltip';
  document.body.appendChild(tooltip);

  // Sélectionner tous les éléments avec la classe product-link
  const productLinks = document.querySelectorAll('.product-link');

  productLinks.forEach(link => {
    // Afficher l'infobulle au survol
    link.addEventListener('mouseenter', function (e) {
      const fullTitle = this.dataset.title;
      if (fullTitle) {
        tooltip.textContent = fullTitle;
        tooltip.style.opacity = '1';

        // Positionnement de l'infobulle
        const rect = this.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';

        // Stocker temporairement le title pour éviter l'infobulle native
        this.dataset.originalTitle = fullTitle;
        this.removeAttribute('title');
      }
    });

    // Cacher l'infobulle quand la souris quitte l'élément
    link.addEventListener('mouseleave', function () {
      tooltip.style.opacity = '0';

      // Restaurer l'attribut title
      if (this.dataset.originalTitle) {
        this.setAttribute('title', this.dataset.originalTitle);
        delete this.dataset.originalTitle;
      }
    });

    // Mettre à jour la position de l'infobulle lors du déplacement de la souris
    link.addEventListener('mousemove', function (e) {
      if (tooltip.style.opacity === '1') {
        tooltip.style.left = (e.pageX + 10) + 'px';
        tooltip.style.top = (e.pageY - tooltip.offsetHeight - 10) + 'px';
      }
    });
  });
});
(function ($) {
  "use strict";
  // Page loading animation
  $(window).on('load', function () {
    $('#js-preloader').addClass('loaded');

    if ($('.cover').length) {
      $('.cover').parallax({
        imageSrc: $('.cover').data('image'),
        zIndex: '1'
      });
    }
    $("#preloader").animate({ 'opacity': 0 }, 600, function () {
      setTimeout(function () {
        $("#preloader").fadeOut(300); // Utilisation de fadeOut directement pour une animation fluide
      }, 300);
    });
  });

  // Scroll event for background header toggle
  $(window).on('scroll', function () {
    const scroll = $(window).scrollTop();
    const box = $('.header-text').height();
    const header = $('header').height();

    if (scroll >= box - header) {
      $("header").addClass("background-header");
    } else {
      $("header").removeClass("background-header");
    }
  });

  // Isotope filter setup
  const elem = document.querySelector('.trending-box');
  const filtersElem = document.querySelector('.trending-filter');

  if (elem) {
    const rdn_events_list = new Isotope(elem, {
      itemSelector: '.trending-items',
      layoutMode: 'masonry'
    });

    if (filtersElem) {
      filtersElem.addEventListener('click', function (event) {
        if (!$(event.target).is('a')) {
          return;
        }

        const filterValue = $(event.target).attr('data-filter');
        rdn_events_list.arrange({
          filter: filterValue
        });

        filtersElem.querySelector('.is_active').classList.remove('is_active');
        $(event.target).addClass('is_active');
        event.preventDefault();
      });
    }
  }


  // Menu elevator animation
  $('.scroll-to-section a[href*=\\#]:not([href=\\#])').on('click', function () {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      let target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        const width = $(window).width();
        if (width < 991) {
          $('.menu-trigger').removeClass('active');
          $('.header-area .nav').slideUp(200);
        }
        $('html, body').animate({
          scrollTop: target.offset().top - 50
        }, 700);
        return false;
      }
    }
  });

})(window.jQuery);

function showToaster(message, bgColor = 'white', delay) {
  // Supprimer tous les toasts existants
  $('.toast').toast('dispose').remove();

  const toastHtml = `
      <div class="toast align-items-center text-black border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" style="background-color: ${bgColor};">
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-red me-2 m-auto" data-bs-dismiss="toast" aria-label="Close">✕</button>
        </div>
      </div>
    `;
  $('#toastContainer').html(toastHtml);
  const $toast = $('.toast').last();
  $toast.toast({ delay: (delay ?? 4000) });
  $toast.toast('show');
}


function showAlert(message, container = "#alerts-place") {
  var alertHtml = `
                <div class="alert alert-warning fade show" role="alert">
                    ${message}
                </div>
            `;
  // Ajout de l'alerte au DOM
  $(container).html(alertHtml);
  setTimeout(function () {
    $(".alert").fadeOut("fast");
  }, 3000);
}
function formatTimeDifference(dateString) {
  // Convertir la chaîne de date en objet Date
  const targetDate = new Date(dateString);
  const currentDate = new Date();

  // Calculer la différence en millisecondes
  const difference = currentDate - targetDate;

  // Convertir la différence en secondes, minutes, heures, jours, mois, années
  const seconds = Math.floor(difference / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);
  const months = Math.floor(days / 30);
  const years = Math.floor(months / 12);

  // Retourner la différence formatée
  if (years > 0) {
    return `${years} an${years > 1 ? 's' : ''}`;
  } else if (months > 0) {
    return `${months} mois`;
  } else if (days > 0) {
    return `${days} jour${days > 1 ? 's' : ''}`;
  } else if (hours > 0) {
    return `${hours} heure${hours > 1 ? 's' : ''}`;
  } else if (minutes > 0) {
    return `${minutes} minute${minutes > 1 ? 's' : ''}`;
  } else {
    return `${seconds} seconde${seconds > 1 ? 's' : ''}`;
  }
}

function handleSearch() {
  const searchText = $('#searchText').val();
  if (searchText.length < 3) {
    showToaster("La recherche doit contenir au moins 3 caractères", "white", 4000);
    return;
  }
  $.ajax({
    type: "post",
    url: "/search/item",
    data: {
      search: searchText,
    },
    dataType: "json",
    success: function (response) {
      $('#search-results-container').removeClass('d-none');
      $('#searchedText').text(searchText);
      $('#search-results').empty();
      $('html, body').animate({
        scrollTop: $('#search-results-container').length ? $('#search-results-container').offset().top - 100 : 0
      }, 800);
      if (response.products.length > 0) {
        $('#search-results-container').css('min-height', '450px');
        $('#search-results').css('height', 'max-content');
        response.products.forEach(function (product) {
          const productCategoryClasses = product.categories.map(category => category.toLowerCase().replace(/\s+/g, '_')).join(' ');
          const link = product.link;
          const image = product.images;
          const title = product.title;
          const old_price = product.old_price;
          const price = product.price;
          const categories = product.categories;
          const html = `
            <div class="col-lg-3 col-md-6 align-self-center mb-30 trending-items ${productCategoryClasses}">
            <div class="item">
              <div class="thumb">
                <a href="${link}">
                  <img
                    src="/assets/images/product-image/${image}"
                    alt="${title}"
                    style="height: 195px;">
                </a>
                <span class="price">
                  ${old_price ? `<em>${old_price}</em>` : ''}
                  ${price}
                </span>
              </div>
              <div class="down-content">
                <span class="category">${categories}</span>
                <h4 class="text-truncate" style="max-width: 200px;">${title}</h4>
                <a href="${link}"><i class="fa fa-shopping-bag"></i></a>
              </div>
            </div>
          </div>
        `;
          $('#search-results').append(html);
        });

        if (response.total_products == 5) {
          $('#search-results-container').css('min-height', '720px');
          const plus = `
          <div class="col-lg-3 col-md-6 align-self-center mb-30 trending-items">
            <div class="item" style="background: #ee626b4a;">
              <div class="thumb" style="background: #ee626b24;">
                <a href="/search/${searchText}">
                  <img
                    src="/assets/images/search.png"
                    alt="plus"
                    style="height: 195px;">
                </a>
                <span class="price">
                  Voir plus
                </span>
              </div>
              <div class="down-content">
                <span class="category"></span>
                <h4>Plus de résultats</h4>
                <a href="/search/${searchText}"><i class="fa fa-shopping-bag"></i></a>
              </div>
            </div>
          </div>
        `;
          $('#search-results').append(plus);
        }
      } else {
        $('#search-results').text('Aucun résultat trouvé!');
      }
    }
  });
}


// Ajout du CSS nécessaire
$('<style>')
  .prop('type', 'text/css')
  .html(`
      /* Style du bouton de zoom */
      .zoom-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 5;
        transition: all 0.3s ease;
      }
      
      .zoom-button:hover {
        background-color: rgba(0, 0, 0, 0.8);
        transform: scale(1.1);
      }
      
      /* Style de la modal de zoom */
      .zoom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        overflow: hidden;
      }
      
      .zoom-modal-content {
        position: relative;
        margin: auto;
        padding: 20px;
        max-width: 90%;
        max-height: 90%;
        top: 50%;
        transform: translateY(-50%);
      }
      
      .zoom-img {
        width: 100%;
        height: auto;
        max-height: 85vh;
        object-fit: contain;
      }
      
      .zoom-close {
        position: absolute;
        top: -30px;
        right: 0;
        color: white;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
      }
      
      .zoom-caption {
        color: white;
        text-align: center;
        padding: 10px 0;
        font-size: 16px;
      }
      
      .no-scroll {
        overflow: hidden;
      }
      
      /* Animation du bouton */
      .img-container {
        position: relative;
      }
      
      .img-container:hover .zoom-button {
        animation: pulse 1.5s infinite;
      }
      
      @keyframes pulse {
        0% {
          box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }
        70% {
          box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
        }
        100% {
          box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
      }
    `)
  .appendTo('head');

function addSubscriber() {
  const email = $('#subscriberEmail').val().trim();

  // Validation de l'email avec une regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email) {
    showToaster(" Veuillez entrer une adresse e-mail.");
    return;
  }
  if (!emailRegex.test(email)) {
    showToaster("Veuillez entrer une adresse e-mail valide.");
    return;
  }
  // Envoi de la requête AJAX si l'email est valide
  $.ajax({
    url: '/subscribe',
    type: 'POST',
    data: {
      email: email
    },
    success: function (response) {
      $('#subscribeButton').prop('disabled', true);
      $('#subscribeButton').css('background-color', '#ccc');
      $('#subscribeButton').css('cursor', 'not-allowed');
      const data = JSON.parse(response);
      if (data.success) {
        showToaster(data.message);
        $('#subscriberEmail').val(''); // Réinitialiser le champ après l'abonnement réussi
      } else {
        showToaster(data.message);
      }
    },
    error: function (response) {
      const data = JSON.parse(response);
      showToaster(data.message);
    }
  });
}
