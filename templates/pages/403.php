

    <div class="container" style="margin-top: 6pc;">
        <style>
            .error-template {
                padding: 40px 15px;
                text-align: center;
            }

            .error-actions {
                margin-top: 40px;
                margin-bottom: 15px;
            }

            .error-actions .btn {
                margin-right: 10px;
            }
        </style>
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1>Oups !</h1>
                    <h2>403</h2>
                    <h2> Accès interdit
                    </h2>
                    <div class="error-details">
                        Vous n’avez pas l’autorisation d’accéder à cette page.
                    </div>
                    <div class="error-actions">
                        <a href="/" class="btn btn-primary btn-lg">
                            <span class="glyphicon glyphicon-home"></span> Accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function() {
                toastr.warning('Vous allez être redirigé vers la page d\'accueil dans quelques secondes...', 'Redirection', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right'
                });
                setTimeout(function() {
                    window.location.href = '/';
                }, 2000);
            }, 1000);
        </script>
        