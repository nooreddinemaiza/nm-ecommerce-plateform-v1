<?php
?>
<!-- Modal -->
<div class="modal fade" id="loginsModal" tabindex="-1" aria-labelledby="loginsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginsModalLabel">Détails de la connexion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <li class="list-group-item"><strong>ID :</strong> <span id="lgmodal-id"></span></li>
                    <li class="list-group-item"><strong>Status:</strong> <span id="lgmodal-status"></span></li>
                    <li class="list-group-item"><strong>Tentatives :</strong> <span id="lgmodal-attempts"></span></li>
                    <li class="list-group-item"><strong>Derniere Tentative :</strong> <span id="lgmodal-last_attempt"></span></li>
                    <li class="list-group-item"><strong>Adresse IP :</strong> <span id="lgmodal-ip"></span></li>
                    <li class="list-group-item"><strong>User Agent :</strong> <span id="lgmodal-agent"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>