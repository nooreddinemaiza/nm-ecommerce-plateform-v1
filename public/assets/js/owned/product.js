$(document).ready(function () {
  if (items.length) {
    $('#cart-items-n').html(items.length).removeClass('d-none');
  }
  // Afficher/masquer le panier
  $('#panier-button').click(function () {
    $('#panier-container').fadeToggle();
    renderCart(items);
  });

  $('#panier-close, #continuer-shopping').click(function () {
    $('#panier-container').fadeOut();
  });

  // Ajouter un produit au panier
  $("#addToCart").on('click', function () {
    const id = $(this).data('id');
    let quantity = $('#quantity').val();
    let data = {
      id: id,
      quantity: quantity
    };

    $.ajax({
      type: "POST",
      url: "/cart/add-product",
      data: data,
      dataType: "JSON",
      success: function (response) {
        if (response.success) {
          items = response.list;
          updateCartCounter(items.length);
          showToaster('Produit ajouté au panier avec succès.');
          renderCart(items);
        } else {
          showToaster('Erreur lors de l\'ajout du produit');
        }
      },
      error: function () {
        showToaster('Erreur lors de l\'ajout du produit');
      }
    });
  });

  // Modifier la quantité d'un produit
  $(document).on('change', '.quantity-input', function () {
    $(this).closest('tr').find('.modify-quantity').removeClass('d-none');
  });

  $(document).on('click', '.modify-quantity', function () {
    $(this).addClass('d-none');
    const $row = $(this).closest('tr');
    const productId = $row.data('product-id');
    const newQuantity = parseInt($row.find('.quantity-input').val());

    if (newQuantity > 0) {
      $.ajax({
        type: "POST",
        url: "/cart/modify-product",
        data: { id: productId, newQuantity: newQuantity },
        dataType: "JSON",
        success: function (response) {
          if (response.success) {
            items = response.list;
            renderCart(items);
            showToaster('Quantité mise à jour avec succès !');
          }
        },
        error: function () {
          showToaster('Erreur lors de la modification de la quantité');
        }
      });
    } else {
      showToaster('La quantité doit être supérieure à 0');
    }
  });

  // Supprimer un produit
  $(document).on('click', '.delete-item-btn', function () {
    const productId = $(this).closest('tr').data('product-id');

    $('#custom-toast').fadeIn();

    $('#toast-cancel').off('click').on('click', function () {
      $('#custom-toast').fadeOut();
    });

    $('#toast-confirm').off('click').on('click', function () {
      removeFromCart(productId);
      $('#custom-toast').fadeOut();
    });
  });
});

function removeFromCart(id) {
  $.ajax({
    type: "POST",
    url: "/cart/delete-product",
    data: { id: id },
    dataType: "JSON",
    success: function (response) {
      if (response.success) {
        items = response.list;
        renderCart(items);
        showToaster('Produit supprimé avec succès !');
      }
    },
    error: function () {
      showToaster('Erreur lors de la suppression du produit');
    }
  });
}

function renderCart(cartItems) {
  $('#cart-items-n').html(cartItems.length);

  const $panierItems = $('#panier-items');
  const $panierContent = $('#panier-content');
  const $panierEmpty = $('#panier-empty');
  const $commandeButtons = $('#commande-buttons');

  $panierItems.empty();

  if (cartItems.length === 0) {
    $('#cart-items-n').addClass('d-none');
    $panierContent.hide();
    $panierEmpty.removeClass('d-none');
    $commandeButtons.addClass('d-none');
  } else {
    $panierContent.show();
    $panierEmpty.addClass('d-none');
    $commandeButtons.removeClass('d-none');

    let total = 0;

    cartItems.forEach(item => {
      const prixUnitaire = parseFloat(item.price);
      const prixTotal = item.final_price; // Utiliser final_price directement
      total += prixTotal;

      const imagePath = item.image.startsWith('http') ? item.image : `/assets/images/product-image/${item.image}`;
      reductionfield = (item.reduction > 0 && item.quantity >= item.appReduction) ? `<span class="text-end small text-success" title="Réduction appliquée : ${item.reduction}%"> (-${item.reduction}%)</span>` : '';
      $panierItems.append(`
        <tr data-product-id="${item.id}">
          <td>
            <a class="d-flex align-items-center" href="${item.link}">
              <img src="${imagePath}" alt="${item.title}" class="product-image me-2">
              <span>${item.title}</span>
            </a>
          </td>
          <td>${prixUnitaire.toFixed(2)} DH</td>
          <td>
            <input type="number" class="form-control form-control-sm quantity-input" value="${item.quantity}" min="1">
          </td>
          <td>${prixTotal.toFixed(2)} DH
              ${reductionfield}
          </td>
          <td>
            <button class="btn btn-sm btn-outline-success modify-quantity d-none" title="Confirmer">
              <i class="fa-solid fa-check"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-item-btn">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `);
    });

    // Afficher le total du panier
    $panierItems.append(`
      <tr class="fw-bold">
        <td colspan="3" class="text-end">Total :</td>
        <td>${total.toFixed(2)} DH</td>
        <td></td>
      </tr>
    `);
  }
}

// Fonction utilitaire pour mettre à jour le compteur du panier
function updateCartCounter(count) {
  const $cartItemsCount = $('#cart-items-n');
  if (count > 0) {
    $('#cart-items-n')
      .addClass('shake')
      .html(items.length)
      .removeClass('d-none')
      .delay(300)
      .queue(function (next) {
        $(this).removeClass('shake');
        next();
      });
  } else {
    $cartItemsCount.addClass('d-none');
  }
}
