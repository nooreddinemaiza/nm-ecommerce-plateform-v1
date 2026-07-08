<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background-color: #0d6efd;
        color: white;
        border-top-left-radius: 1rem !important;
        border-top-right-radius: 1rem !important;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
</style>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header text-center py-3">
                    <h3 class="mb-0"><i class="fas fa-box-open me-2"></i>Consulter votre commande</h3>
                </div>
                <div class="card-body p-4" style="margin: 0 auto;">
                    <?php
                    if (isset($_SESSION['order_consult']) && $_SESSION['order_consult'] > 3) {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">imite d'entrées erronées atteinte</h4>
                            <p class="mb-0">
                                Vous êtes bloqué de notre service de consultation
                                Veuillez contactez notre service pour plus d'information sur votre commande
                            </p>

                        </div>
                        <?php
                    } else {
                        if (!isset($data["captcha"])) {
                        ?>
                            <form id="orderForm" action="/order/find" method="POST">
                                <div id="orderError" class="mb-4 <?= $data['notFound'] ? 'd-block' : 'd-none' ?>">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <span id="errorMessage">Aucune commande trouvée. Veuillez vérifier vos informations.</span>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token'] ?>">
                                    <label for="contactInfo" class="form-label">Email ou Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="needle" id="contactInfo" placeholder="Entrez votre email ou téléphone" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="orderReference" class="form-label">Référence de commande</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        <input type="text" class="form-control" name="reference" id="orderReference" placeholder="Entrez votre référence de commande" required>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button id="consulter" type="submit" class="btn btn-primary btn-lg"><i class="fas fa-search me-2"></i>Consulter</button>
                                </div>
                            </form>
                    <?php
                        } else {
                            echo $data["captcha"];
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>