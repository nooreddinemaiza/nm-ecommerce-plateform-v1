    $(document).ready(function() {
        // Animation à la confirmation
        $("#toast-confirm").on("click", function() {
            var $toast = $("#custom-toast");

            // Changement du message et de l'icône
            $toast.find(".toast-message").text("Suppression confirmée !");
            $toast.find(".toast-icon i").removeClass("fa-exclamation-triangle").addClass("fa-check-circle");

            // Application des effets visuels
            $toast.addClass("toast-confirming animate__animated animate__pulse");

            // Animation des boutons
            $toast.find(".toast-actions").fadeOut(300);

            // Disparition du toast après les effets
            setTimeout(function() {
                $toast.addClass("animate__fadeOutRight");
                setTimeout(function() {
                    $toast.hide();
                    // Réinitialisation pour la prochaine utilisation
                    setTimeout(function() {
                        $toast.removeClass("toast-confirming animate__animated animate__pulse animate__fadeOutRight");
                        $toast.find(".toast-message").text("Confirmez la suppression");
                        $toast.find(".toast-icon i").removeClass("fa-check-circle").addClass("fa-exclamation-triangle");
                        $toast.find(".toast-actions").show();
                    }, 300);
                }, 800);
            }, 1200);
        });

        // Animation à l'annulation
        $("#toast-cancel").on("click", function() {
            var $toast = $("#custom-toast");

            // Changement du message et de l'icône
            $toast.find(".toast-message").text("Action annulée");
            $toast.find(".toast-icon i").removeClass("fa-exclamation-triangle").addClass("fa-times-circle");

            // Application des effets visuels
            $toast.addClass("toast-canceling animate__animated animate__headShake");

            // Animation des boutons
            $toast.find(".toast-actions").fadeOut(300);

            // Disparition du toast après les effets
            setTimeout(function() {
                $toast.addClass("toast-hide");
                setTimeout(function() {
                    $toast.hide();
                    // Réinitialisation pour la prochaine utilisation
                    setTimeout(function() {
                        $toast.removeClass("toast-canceling animate__animated animate__headShake toast-hide");
                        $toast.find(".toast-message").text("Confirmez la suppression");
                        $toast.find(".toast-icon i").removeClass("fa-times-circle").addClass("fa-exclamation-triangle");
                        $toast.find(".toast-actions").show();
                    }, 300);
                }, 500);
            }, 800);
        });
    });