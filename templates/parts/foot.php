        
        <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 2055;"></div>
        <script>
            let items = Object.values(<?= json_encode($_SESSION['CART']['items'] ?? []); ?>);
        </script>
        <!-- <script src="/assets/js/vendor/jquery-3.6.0.min.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js"></script>
        <script src="/assets/js/vendor/bootstrap.bundle.min.js"></script>
        <!-- Sweet Alert pour les notifications -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        <script src="/assets/js/owned/product.js"></script>
        
        <?= $js ?? '' ?>

    </body>
</html>