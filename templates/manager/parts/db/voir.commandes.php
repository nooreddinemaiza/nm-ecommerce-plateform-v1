<!-- Modal -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordersModalLabel">Détails de la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <li class="list-group-item"><strong>ID :</strong> <span id="orderModal-id"></span></li>
                    <li class="list-group-item"><strong>Nom client :</strong> <span id="orderModal-customer_name"></span></li>
                    <li class="list-group-item"><strong>Email client :</strong> <span id="orderModal-customer_email"></span></li>
                    <li class="list-group-item"><strong>Téléphone client :</strong> <span id="orderModal-customer_phone"></span></li>
                    <li class="list-group-item"><strong>Date de commande :</strong> <span id="orderModal-order_date"></span></li>
                    <li class="list-group-item"><strong>Statut :</strong> <span id="orderModal-status"></span></li>
                    <li class="list-group-item"><strong>Imprimée :</strong> <span id="orderModal-printed"></span></li>
                    <li class="list-group-item"><strong>Ville :</strong> <span id="orderModal-customer_city"></span></li>
                    <li class="list-group-item"><strong>Code postal :</strong> <span id="orderModal-customer_city_zip"></span></li>
                    <li class="list-group-item"><strong>Adresse :</strong> <span id="orderModal-customer_address"></span></li>
                    <li class="list-group-item"><strong>Montant total :</strong> <span id="orderModal-total_amount"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
