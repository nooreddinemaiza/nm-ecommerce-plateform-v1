<?php
?>
<!-- Modal -->
<div class="modal fade" id="clientMessageModal" tabindex="-1" aria-labelledby="clientMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientMessageModalLabel">Messages Clients</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <li class="list-group-item"><strong>ID :</strong> <span id="modal-id"></span></li>
                    <li class="list-group-item"><strong>Nom :</strong> <span id="modal-name"></span></li>
                    <li class="list-group-item"><strong>Email :</strong> <span id="modal-email"></span></li>
                    <li class="list-group-item"><strong>Sujet :</strong> <span id="modal-subject"></span></li>
                    <li class="list-group-item"><strong>Message :</strong> <span id="modal-message"></span></li>
                    <li class="list-group-item"><strong>Adresse IP :</strong> <span id="modal-ip"></span></li>
                    <li class="list-group-item"><strong>User Agent :</strong> <span id="modal-agent"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>