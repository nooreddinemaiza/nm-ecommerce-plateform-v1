<?php

use Src\Helpers\Config;
use Src\Helpers\Helper;

$order_infos = [
    'produits' => $data['order_infos']['produits'],
    'client' => $data['order_infos']['client']
];
$_SESSION['order_infos'] = $order_infos;
if (isset($_SESSION['printed'])) {
    unset($_SESSION['printed']);
}
?>
<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="display-4 text-primary fw-bold">
            <a href="/">
                <img src="<?= WEB_LOGO_URL ?>" alt="<?= WEB_NAME ?>" style="width: 158px; background-color: rgba(var(--bs-link-color-rgb),var(--bs-link-opacity,1));"> <?= WEB_NAME ?>
            </a>
        </h1>
        <p class="text-muted lead">Votre partenaire de confiance pour vos achats en ligne</p>
        <div class="d-flex justify-content-center">
            <div class="border-bottom border-primary" style="width: 100px; height: 3px;"></div>
        </div>
    </div>

    <div class="order-summary bg-white p-3 p-md-4 rounded shadow-lg border border-light">
        <div class="position-relative mb-4 bg-light p-3 rounded-3 border-start border-primary border-5">
            <h2 class="text-center mb-0 text-primary">
                <i class="fas fa-receipt me-2"></i> Récapitulatif de Commande
            </h2>
        </div>
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle fs-4 me-2"></i>
                    <h5 class="mb-0">Informations Client</h5>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="p-2 me-3 text-primary">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Nom et Prénom</small>
                                <strong><?= $order_infos['client']['nom_prenom'] ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="p-2 me-3 text-primary">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Statut</small>
                                <strong id="status"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="p-2 me-3 text-primary">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Adresse de livraison</small>
                                <strong><?= $order_infos['client']['address'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        Détails de la Commande
                    </h5>
                    <span class="text-white">Fait le : <?= $order_infos['client']['date'] ?></span>

                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3 border-0">Produit</th>
                                <th class="px-3 py-3 border-0 text-center">Quantité</th>
                                <th class="px-3 py-3 border-0 text-end">Prix Unitaire</th>
                                <th class="px-3 py-3 border-0 text-end">Réduction</th>
                                <th class="px-3 py-3 border-0 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_commande = 0;
                            foreach ($order_infos['produits'] as $index => $produit):
                                $reduction = ($produit['quantity'] >= $produit['appReduction']['plus']  && $produit['appReduction']['reduction'] != 0) ? $produit['appReduction']['reduction'] : 0;
                                $total_ligne = ($produit['unit_price'] * $produit['quantity']) - ($produit['unit_price'] * $produit['quantity'] * $reduction / 100);
                                $total_commande += $total_ligne;
                            ?>
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="d-flex align-items-center">
                                            <span><?= $produit['title'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center"><?= $produit['quantity'] ?></td>
                                    <td class="px-3 py-3 text-end">
                                        <?= (fmod($produit['unit_price'], 1) != 0) ? number_format($produit['unit_price'], 2) : number_format($produit['unit_price'], 0) ?> DH
                                    </td>
                                    <td class="px-3 py-3 text-end">
                                        <?= $reduction ?>%
                                    </td>
                                    <td class="px-3 py-3 text-end fw-bold">
                                        <?= (fmod($total_ligne, 1) != 0) ? number_format($total_ligne, 2) : number_format($total_ligne, 0) ?> DH
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-7 text-center">
                <div class="bg-light p-3 rounded mt-3">
                    <p class="fw-bold text-primary">Merci pour votre commande!</p>
                    <p class="small text-muted">
                        Pour toute question, contactez-nous au <?= Config::get("WEB_PHONE") ?? '+212 5XX XXX XXX' ?>
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-5 bg-light p-4 rounded">
                <h5 class="border-bottom pb-2 mb-3">Récapitulatif</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Sous-total</span>
                    <span><?= (fmod($total_commande, 1) != 0) ? number_format($total_commande, 2) : number_format($total_commande, 0) ?> DH</span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top text-primary">
                    <span>TOTAL</span>
                    <span><?= (fmod($total_commande, 1) != 0) ? number_format($total_commande, 2) : number_format($total_commande, 0) ?> DH</span>
                </div>
            </div>
        </div>
        <div class="">
            <div class="d-flex justify-content-center">
                <button type="submit" id="generate_pdf" class="btn btn-primary btn-lg w-md-auto px-4 me-md-2" <?= $order_infos['client']['printed'] == 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-file-pdf me-2"></i> Télécharger PDF
                    <span class="text-muted d-none" id="loading_pdf">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
        <div class="text-center mt-4">
            <?php
            $time_difference = Helper::TimeDifference($order_infos['client']['date']);
            if ($order_infos['client']['status'] == "processing" && $time_difference['jours'] < 1): ?>

                <div class="alert alert-info mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Information importante :</strong> Vous pouvez annuler votre commande dans un délai de <strong><?= Config::get("WEB_CANCEL_ORDER_LIMIT") ?></strong> à compter de la date de commande.
                        </div>
                    </div>
                </div>
                <button id="cancel_order" class="btn btn-danger btn-lg w-md-auto px-4 mt-2 mt-md-0">
                    <i class="fas fa-times me-2"></i> Annuler la commande
                </button>
            <?php endif; ?>
            <a href="/" class="btn btn-outline-secondary btn-lg w-md-auto px-4 mt-2 mt-md-0">
                <i class="fas fa-home me-2"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>
<div id="custom-toast" data-confirm="" data-index="" class="custom-toast" style="z-index: 2099 !important; bottom: 90px; display: none;">
    <p>Voulez-vous vraiment annuler cette commande ?</p>
    <button id="toast-confirm">Confirmer</button>
    <button id="toast-cancel" onclick="$('#custom-toast').fadeOut()">Annuler</button>
</div>
<script>
    // Ajouter une classe au statut pour le colorer
    function setStatus() {
        let statusClass = "";
        let status = "";
        switch ("<?= $order_infos['client']['status'] ?>") {
            case "pending":
                statusClass = "text-warning";
                status = "En attente";
                break;
            case "processing":
                statusClass = "text-info";
                status = "En cours de traitement";
                break;
            case "shipped":
                statusClass = "text-primary";
                status = "En cours de livraison";
                break;
            case "delivered":
                statusClass = "text-success";
                status = "Livré";
                break;
        }
        $('#status').addClass(statusClass);
        $('#status').text(status);
    }
    setStatus();
    // Fonction d'impression des détails de commande
    $("#generate_pdf").click(function() {
        // Afficher l'indicateur de chargement
        $("#loading_pdf").removeClass("d-none").html(`
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Préparation du document...</p>
        </div>
    `);

        // Créer une copie du contenu à imprimer
        let printContent = $(".order-summary").clone();

        // Nettoyer le contenu pour l'impression
        printContent.find(".no-print, #generate_pdf, #cancel_order, .btn").remove();

        // Ajouter un en-tête professionnel
        const header = `
        <div class="print-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0"><?= Config::get("WEB_NAME") ?></h2>
                    <p class="text-muted mb-0"><?= Config::get("WEB_ADDRESS") ?></p>
                </div>
                <div class="text-end">
                    <h4 class="text-primary mb-0">Commande #<?= $order_infos['client']['id'] +  Config::get("ORDER_ID_NUMBER") ?></h4>
                    <p class="text-muted mb-0">${new Date().toLocaleDateString('fr-FR')}</p>
                </div>
            </div>
            <hr class="my-3">
        </div>
    `;

        // Ajouter un pied de page
        const footer = `
        <div class="print-footer mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-6">
                    <p class="small text-muted">Merci pour votre confiance</p>
                </div>
                <div class="col-6 text-end">
                    <p class="small text-muted">Document généré le ${new Date().toLocaleString('fr-FR')}</p>
                </div>
            </div>
        </div>
    `;

        // Style professionnel pour l'impression
        let printStyles = `
        <style>
            @media print {
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    color: #333;
                    line-height: 1.5;
                    font-size: 14px;
                }
                .print-container { 
                    max-width: 800px; 
                    margin: 0 auto;
                    padding: 20px;
                }
                .print-header {
                    border-bottom: 2px solid #0d6efd;
                    padding-bottom: 10px;
                }
                .print-footer {
                    font-size: 12px;
                }
                h2, h3, h4 {
                    color: #0d6efd;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                .table th {
                    background-color: #f8f9fa;
                    color: #495057;
                    padding: 10px;
                    text-align: left;
                    border-bottom: 2px solid #dee2e6;
                }
                .table td {
                    padding: 8px 10px;
                    border-bottom: 1px solid #dee2e6;
                    vertical-align: top;
                }
                .table-striped tbody tr:nth-of-type(odd) {
                    background-color: rgba(0, 0, 0, 0.02);
                }
                .bg-primary {
                    background-color: #0d6efd !important;
                    color: white !important;
                }
                .text-primary { color: #0d6efd !important; }
                .text-success { color: #198754 !important; }
                .text-danger { color: #dc3545 !important; }
                .text-warning { color: #fd7e14 !important; }
                .rounded { border-radius: 4px !important; }
                .border { border: 1px solid #dee2e6 !important; }
                .p-3 { padding: 1rem !important; }
                .mb-3 { margin-bottom: 1rem !important; }
                .mt-3 { margin-top: 1rem !important; }
                .alert, .no-print, .btn, .toast {
                    display: none !important;
                }
                @page {
                    size: A4;
                    margin: 15mm;
                }
            }
        </style>
    `;

        // Créer une nouvelle fenêtre pour l'impression
        let printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <html>
        <head>
            <title>Commande <?= $order_infos['client']['id'] + Config::get("ORDER_ID_NUMBER") ?></title>
            <meta charset="UTF-8">
            ${printStyles}
        </head>
        <body>
            <div class="print-container">
                ${header}
                ${printContent.html()}
                ${footer}
            </div>
        </body>
        </html>
    `);

        // Gestion de l'impression
        printWindow.document.close();
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                $("#loading_pdf").addClass("d-none");

                // Fermer la fenêtre après impression (sauf sur certains navigateurs)
                setTimeout(() => {
                    printWindow.close();
                }, 500);
            }, 300);
        };
    });

    $("#cancel_order").click(function() {
        $("#custom-toast").fadeIn();
        $("#custom-toast").data("confirm", "order");
        $("#custom-toast").data("index", "delete");
    });
    $("#toast-confirm").click(function() {
        switch ($("#custom-toast").data("confirm")) {
            case "order":
                $.ajax({
                    url: "/order/cancel",
                    type: "POST",
                    data: {
                        order_id: <?= $data['order_id'] ?>
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            $("#orderDetailsModal").modal("hide");
                            $("#custom-toast").fadeOut();
                            $("#ordersModal").modal("show");
                            showToaster(response.message);
                            setTimeout(function() {
                                toastr.warning('Vous allez être redirigé vers la page d\'accueil dans quelques secondes...', 'Redirection', {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right'
                                });
                                setTimeout(function() {
                                    window.location.href = '/';
                                }, 5000);
                            }, 1000);
                        } else {
                            showToaster(response.message);
                        }
                    },
                    error: function() {
                        showToaster("Erreur lors de la suppression de la commande.");
                    }
                });
                break;
            default:
                break;
        }
    });
</script>