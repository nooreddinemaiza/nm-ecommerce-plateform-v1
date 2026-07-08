<div class="page-heading" style="padding: 0 !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="logo">
                    <img src="<?= WEB_LOGO_URL?>" alt="" srcset="">
                </div>
                <h3>Maintenance</h3>
            </div>
        </div>
    </div>
</div>
<style>
    .logo{
        width: 220px;
        padding: 10px;
        margin: 0 auto;
    }
    .maintenance-container {
        margin: auto;
        text-align: center;
    }

    .maintenance-icon {
        font-size: 100px;
        color: rgb(0, 89, 255);
        animation: shake 1s infinite alternate;
    }

    @keyframes shake {
        0% {
            transform: rotate(-5deg);
        }

        100% {
            transform: rotate(5deg);
        }
    }
</style>

<div class="contact-page section">
    <div class="maintenance-container">
        <i class="fas fa-tools maintenance-icon"></i>
        <h2 class="mt-3">Notre boutique est en maintenance</h2>
        <p class="text-muted">Nous améliorons notre site pour mieux vous servir. Revenez bientôt !</p>
        <a href="/contact" class="btn btn-outline-secondary mt-3">Contactez-nous</a>
    </div>
</div>