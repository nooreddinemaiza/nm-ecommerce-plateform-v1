<?php

use Src\Helpers\Config;

$sectR = new Src\Helpers\SectionRenderer('home');

foreach (array_keys($data) as $k => $v) {
    ${$v} = $data[$v];
}
$pageData = json_decode($pageContent[0]['page_data'], true);
?>
<style>
    footer h4 {
        font-size: 1.5rem;
        font-weight: 700;
        color: white !important;
    }
</style>
<footer>
    <?= $sectR->footer(); ?>
    <!-- Icône WhatsApp flottante -->
    <a href="https://api.whatsapp.com/send/?phone=<?= $pageData['phone'] ?>&type=phone_number&app_absent=0"
        class="btn btn-sm d-flex align-items-center justify-content-center gap-2 whatsapp-float"
        target="_blank"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Cliquez ici pour discuter sur WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Active le tooltip -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <div class="container">
        <!-- Contenu principal -->
        <div class="text-center text-white position-relative">
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 me-1">
                                <i class="fas fa-map-marker-alt fa-sm text-white"></i>
                            </div>
                            <span><?= Config::get('WEB_ADDRESS') ?? 'Adresse commerciale' ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-white opacity-25">

            <div class="mt-4">
                <p class="mb-0 opacity-75">&copy; <?= date('Y') ?> <a href="/"><?= WEB_NAME ?></a></p>
            </div>
        </div>
    </div>
</footer>