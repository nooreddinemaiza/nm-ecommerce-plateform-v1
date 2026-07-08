<?php

$data = \Src\Helpers\Helper::safeJsonDecode($data, "pageContent");

foreach (array_keys($data) as $k => $v) {
    ${$v} = $data[$v];
}
$pageData = json_decode($pageContent[0]['page_data'], true);
?>
<div class="page-heading header-text" style="background-color:rgb(0, 89, 255);">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Contactez Nous</h3>
                <span class="breadcrumb"><a href="/">Acceuil</a> > Contactez Nous </span>
            </div>
        </div>
    </div>
</div>



<div class="contact-page section">
    <div class="container">
        <div class="row gx-5"> 
            <div class="col-lg-6 align-self-center">
                <div class="row left-text">
                    <div class="col-lg-12">
                        <div class="section-heading">
                            <h6>Contactez-nous</h6>
                            <h2><?= $pageData['title'] ?></h2>
                        </div>
                        <p><?= $pageData['introduction'] ?></p>
                        <ul class="list-unstyled">
                            <li><span class="fw-bold">Adresse :</span> <?= $pageData['address'] ?></li>
                            <li class="my-3">
                                <div class="w-100 rounded overflow-hidden" style="max-width: 100%;">
                                    <div id="map" class="ratio ratio-16x9">
                                        <?php
                                        if (!empty($pageData['map'])) {
                                            echo ($pageData['map']);
                                        } else {
                                        ?>
                                            <iframe src=""></iframe>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                            <li><span class="fw-bold">Téléphone :</span> <?= $pageData['phone'] ?></li>
                            <li><span class="fw-bold">Email :</span> <?= $pageData['email'] ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" id="contact-us-area" style="padding: 25px;background:#f5f5f5;border-radius: 25px;">
                <div class="right-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="contact-form">
                                <div class="row gy-3"> <!-- gy-3 pour espacement vertical -->
                                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $csrf_token ?>">

                                    <div class="col-12">
                                        <fieldset>
                                            <small class="error-message" id="name-error"></small>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Votre Nom...">
                                        </fieldset>
                                    </div>

                                    <div class="col-12">
                                        <fieldset>
                                            <small class="error-message" id="email-error"></small>
                                            <input type="text" name="email" id="email" class="form-control" placeholder="Votre E-mail...">
                                        </fieldset>
                                    </div>

                                    <div class="col-12">
                                        <fieldset>
                                            <small class="error-message" id="subject-error"></small>
                                            <input type="text" name="subject" id="subject" class="form-control" placeholder="Sujet...">
                                        </fieldset>
                                    </div>

                                    <div class="col-12">
                                        <fieldset>
                                            <small class="error-message" id="message-error"></small>
                                            <textarea name="message" id="message" class="form-control" placeholder="Votre Message"></textarea>
                                        </fieldset>
                                    </div>

                                    <div class="col-12">
                                        <fieldset>
                                            <div id="captchaContainer"></div>
                                        </fieldset>
                                    </div>

                                    <div class="col-12">
                                        <fieldset>
                                            <button type="submit" id="form-submit" class="orange-button">Envoyer</button>
                                        </fieldset>
                                    </div>
                                </div>
                            </form>

                            <style>
                                .error-message {
                                    color: red;
                                    font-size: 0.9em;
                                    display: none;
                                    margin-left: 10px;
                                }

                                input:invalid,
                                textarea:invalid {
                                    border: 1px solid red;
                                }
                            </style>

                            <script>
                                $(document).ready(function() {
                                    $("#contact-form").on("submit", function(event) {
                                        event.preventDefault(); // Empêche l'envoi traditionnel du formulaire

                                        let isValid = true;

                                        // Validation du Nom
                                        let name = $("#name").val();
                                        if (name.trim().length < 3) {
                                            $("#name-error").text("Le nom doit contenir au moins 3 caractères.").show();
                                            isValid = false;
                                        } else {
                                            $("#name-error").hide();
                                        }

                                        // Validation de l'Email
                                        let email = $("#email").val();
                                        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                                        if (!emailPattern.test(email)) {
                                            $("#email-error").text("Veuillez entrer une adresse email valide.").show();
                                            isValid = false;
                                        } else {
                                            $("#email-error").hide();
                                        }

                                        // Validation du Sujet
                                        let subject = $("#subject").val();
                                        if (subject.trim().length < 3) {
                                            $("#subject-error").text("Le sujet doit contenir au moins 3 caractères.").show();
                                            isValid = false;
                                        } else {
                                            $("#subject-error").hide();
                                        }

                                        // Validation du Message
                                        let message = $("#message").val();
                                        if (message.trim().length < 10) {
                                            $("#message-error").text("Le message doit contenir au moins 10 caractères.").show();
                                            isValid = false;
                                        } else {
                                            $("#message-error").hide();
                                        }

                                        // Si le formulaire est valide, envoi via AJAX
                                        if (isValid) {
                                            $.ajax({
                                                type: "POST",
                                                url: "/contact/get-captcha",
                                                success: function(response) {
                                                    response = JSON.parse(response)
                                                    if (response.success) {
                                                        $("#captchaContainer").html(response.data);
                                                        $("#form-submit").remove();
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    // Gestion des erreurs
                                                    showToaster("Une erreur s'est produite lors de l'envoi du message. Veuillez réessayer.");
                                                    console.error(xhr, status, error);
                                                }
                                            });
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>