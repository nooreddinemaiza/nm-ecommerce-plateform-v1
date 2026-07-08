<style>
    .order-id {
        width: 5% !important;
        ;
    }

    .order-client {
        width: 25% !important;
        ;
    }

    .order-quantity {
        width: 5% !important;
    }
</style>
<div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordersModalLabel">Liste des Commandes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchOrders" class="form-control" placeholder="Rechercher...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th class="order-id">ID</th>
                                <th class="order-client">Client</th>
                                <th class="order-quantity">Quantité</th>
                                <th class="order-status">
                                    <select id="statusFilter" class="form-select form-select-sm bg-light border-primary rounded-3 shadow-sm">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="processing">En traitement</option>
                                        <option value="shipped">Expédié</option>
                                        <option value="delivered">Livré</option>
                                        <option value="cancelled">Annulé</option>
                                        <option value="returned">Retourné</option>
                                    </select>
                                </th>
                                <th class="order-date">
                                    <span>Date</span>
                                    <button class="btn" style="color: white;" id="ordersSortBtn" data-ordersNewOnly="1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </th>
                                <th class="order-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Les commandes seront insérées ici via AJAX -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-between mt-3">
                    <button id="orderPrevPage" class="btn btn-outline-primary" disabled>Précédent</button>
                    <span id="paginationInfo"></span>
                    <button id="orderNextPage" class="btn btn-outline-primary">Suivant</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails de l'Ordre -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsLabel">Détails de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Données du client</h5>
                        <p><strong>Nom et Prénom :</strong> <span id="orderClientName"></span></p>
                        <p><strong>Adresse :</strong> <span id="orderClientAddress"></span></p>
                        <p><strong>Téléphone :</strong> <span id="orderClientPhone"></span></p>
                        <p><strong>Email :</strong> <span id="orderClientEmail"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Informations de commande</h5>
                        <p><strong>Référence :</strong> <span id="orderId"></span></p>
                        <p><strong>Date de la commande :</strong> <span id="orderDate"></span></p>
                        <p><strong>Statut :</strong> <span id="orderStatus"></span></p>
                    </div>
                </div>
                <hr>
                <div style="overflow-x: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody id="orderProductsTableBody">
                        </tbody>
                    </table>
                </div>
                <h5>Tableau des produits</h5>
                <p><strong>Total :</strong> <span id="orderTotal"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnSendEmail"><i class="fa fa-envelope"></i> Envoyer un email</button>
                <button type="button" class="btn btn-primary" id="btnChangeStatus"><i class="fa fa-edit"></i> Modifier le statut</button>
                <button type="button" class="btn btn-danger btn-delete-order" id="btnDeleteOrder"><i class="fa fa-trash"></i> Supprimer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour changer le statut -->
<div class="modal fade" id="statusToast" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Modifier le statut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <select id="newStatusSelect" class="form-select">
                        <option value="pending">En attente</option>
                        <option value="processing">En traitement</option>
                        <option value="shipped">Expédié</option>
                        <option value="delivered">Livré</option>
                        <option value="cancelled">Annulé</option>
                        <option value="returned">Retourné</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnConfirmStatus">Valider</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal pour l'envoi de l'email -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendEmailModalLabel">Envoyer un email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sendEmailForm">
                    <div class="mb-3">
                        <label for="emailRecipient" class="form-label">Destinataire</label>
                        <input type="email" class="form-control" id="emailRecipient" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Sujet</label>
                        <input type="text" class="form-control" id="emailSubject" placeholder="Sujet de l'email" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailBody" class="form-label">Message</label>
                        <textarea class="form-control" id="emailBody" rows="5" placeholder="Contenu de l'email" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="messageClient" data-index="order">Envoyer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>