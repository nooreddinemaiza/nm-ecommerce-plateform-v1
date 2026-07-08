$(document).ready(function () {
    renderCart(items);
    // Variables globales
    let currentStep = 1;
    const totalSteps = 2; // Modifié de 3 à 2 étapes
    // Sanitization and validation utility functions
    const Validator = {
        // Sanitize input to prevent XSS
        sanitizeInput: function (input) {
            if (typeof input !== 'string') return input;
            return input.replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#x27;')
                .replace(/\//g, '&#x2F;');
        },

        // Validate email format
        isValidEmail: function (email) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailRegex.test(email);
        },

        // Validate phone number (allows international formats)
        isValidPhone: function (phone) {
            const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
            return phoneRegex.test(phone);
        },

        // Validate name (no numbers or special characters)
        isValidName: function (name) {
            const nameRegex = /^[a-zA-ZÀ-ÿ\s'-]+$/;
            return nameRegex.test(name);
        },

        // Validate address
        isValidAddress: function (address) {
            // Allow letters, numbers, spaces, and some punctuation
            const addressRegex = /^[a-zA-Z0-9\s,'-]+$/;
            return addressRegex.test(address);
        }
    };

    // Enhanced form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = [{
            id: 'first_name',
            validator: Validator.isValidName,
            errorMsg: 'Nom invalide'
        },
        {
            id: 'last_name',
            validator: Validator.isValidName,
            errorMsg: 'Prénom invalide'
        },
        {
            id: 'email',
            validator: Validator.isValidEmail,
            errorMsg: 'Email invalide'
        },
        {
            id: 'phone',
            validator: Validator.isValidPhone,
            errorMsg: 'Numéro de téléphone invalide'
        },
        {
            id: 'address',
            validator: Validator.isValidAddress,
            errorMsg: 'Adresse invalide'
        }
        ];

        requiredFields.forEach(field => {
            const input = $(`#${field.id}`);
            const value = input.val().trim();
            const errorElement = $(`#${field.id}-feedback`);

            // Sanitize input
            input.val(Validator.sanitizeInput(value));

            // Validate input
            if (!value) {
                input.addClass('is-invalid');
                errorElement.text('Ce champ est requis').show();
                isValid = false;
            } else if (!field.validator(value)) {
                input.addClass('is-invalid');
                errorElement.text(field.errorMsg).show();
                isValid = false;
            } else {
                input.removeClass('is-invalid');
                errorElement.hide();
            }
        });

        return isValid;
    }

    // Animation au chargement de la page
    setTimeout(function () {
        $('.card').addClass('animate__animated animate__fadeInUp');
    }, 300);

    // Navigation entre les étapes
    function goToStep(step) {
        // Masquer toutes les sections
        $('.form-step').removeClass('active');

        // Afficher la section appropriée
        if (step === 1) {
            $('#products-section').addClass('active');
            $('.progress-bar').css('width', '50%'); // Modifié à 50%
        } else if (step === 2) {
            $('#shipping-section').addClass('active');
            $('.progress-bar').css('width', '100%'); // Change à 100%
        } else if (step === 3) {
            // Étape 3 supprimée, mais transition vers le récapitulatif conservée
            $('#summary-section').show();
            $('.form-step').hide();
            $('.progress-bar').css('width', '100%');
        }

        // Mettre à jour les indicateurs d'étape
        $('.step').removeClass('active completed');
        for (let i = 1; i <= totalSteps; i++) {
            if (i < step) {
                $(`.step:nth-child(${i})`).addClass('completed');
            } else if (i === step) {
                $(`.step:nth-child(${i})`).addClass('active');
            }
        }

        // Mettre à jour l'étape courante
        currentStep = step;

        // Défiler vers le haut de la page avec une animation
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    }

    // Événements de navigation
    $('#to-shipping').click(function () {
        if (items.length) {
            goToStep(2);
        } else {
            setTimeout(function () {
                showToaster('Vous allez être redirigé vers la boutique dans 5 secondes...');
                setTimeout(function () {
                    window.location.href = '/shop';
                }, 5000);
            }, 100);
        }
    });

    $('#back-to-products').click(function () {
        goToStep(1);
    });
    $('#shipping-form').submit(function (e) {
        e.preventDefault();
        if (!validateForm()) {
            $(this).addClass('was-validated');
            return;
        }

        // Animation lors de la validation
        $('#shipping-section .card').addClass('animate__animated animate__fadeOutUp');
        setTimeout(function () {
            $('#shipping-section .card').removeClass('animate__animated animate__fadeOutUp');
            // Passer directement au récapitulatif (étape 3)
            updateSummary();
            goToStep(3);
        }, 500);
    });


    $('#back-to-shipping').click(function () {
        $('#summary-section').hide();
        goToStep(2);
    });

    // Mettre à jour le récapitulatif avec les informations saisies
    function updateSummary() {
        // Adresse de livraison
        const firstName = $('#first_name').val();
        const lastName = $('#last_name').val();
        const address = $('#address').val();
        const postalCode = $('#postal_code').val() || '';
        const city = $('#city').val() || '';
        const phone = $('#phone').val();

        // Mise à jour de l'adresse
        let addressText = `${firstName} ${lastName}<br>${address}`;
        if (postalCode || city) {
            addressText += `<br>${postalCode} ${city}`;
        }
        $('.delivery-address').html(addressText);

        // Mise à jour du numéro de téléphone
        $('#confirmation-phone').text(phone);
        $('#confirmation-phone-final').text(phone);

        // Email de confirmation
        $('#confirmation-email').text($('#email').val());

    }

    // Confirmation de la commande
    $('#confirm-order').click(function () {
        // Animation du bouton
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i> Traitement en cours...');
        $(this).prop('disabled', true);

        // Collecter les données du formulaire
        const orderData = {
            csrf_token: $('#csrf_token').val(),
            firstName: $('#first_name').val(),
            lastName: $('#last_name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            address: $('#address').val(),
            postalCode: $('#postal_code').val(),
            city: $('#city').val(),
        };

        // Envoyer les données via AJAX
        $.ajax({
            url: '/checkout-confirmed',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(orderData),
            success: function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    // Traitement en cas de succès
                    $('#summary-section').addClass('animate__animated animate__fadeOutUp');
                    setTimeout(function () {
                        $('#summary-section').hide().removeClass('animate__animated animate__fadeOutUp');
                        $('#confirmation-section').show().addClass('animate__animated animate__fadeIn');
                        // Récupérer le numéro de commande
                        $('#order-number').text(response.order_id);
                        $('#order-link').attr('href', '/order/' + response.order_id);
                        // Confettis pour célébrer l'achat
                        confetti({
                            particleCount: 100,
                            spread: 70,
                            origin: {
                                y: 0.6
                            }
                        });
                    }, 500);
                } else {
                    showToaster(response.message);
                }
            },
            error: function (xhr, status, error) {
                // Traitement en cas d'erreur
                $('#confirm-order').html('<i class="fas fa-check me-2"></i> Confirmer la commande');
                $('#confirm-order').prop('disabled', false);
                alert('Une erreur s\'est produite lors de la confirmation de la commande. Veuillez réessayer.');
            }
        });
    });


    // Option 1 : Confettis simples
    function confetti(options = {}) {
        // Vérifier si la bibliothèque canvas-confetti est chargée
        if (typeof window.confetti !== 'function') {
            console.warn('La bibliothèque canvas-confetti n\'est pas chargée');
            return;
        }

        // Options par défaut
        const defaultOptions = {
            particleCount: 100, // Nombre de confettis
            spread: 70, // Angle de dispersion
            origin: {
                y: 0.6
            }, // Point de départ
            colors: [
                '#26ccff',
                '#a25afd',
                '#ff5e7e',
                '#88ff5a',
                '#fcff42'
            ]
        };

        // Fusionner les options personnalisées avec les options par défaut
        const finalOptions = {
            ...defaultOptions,
            ...options
        };

        // Lancer les confettis
        window.confetti(finalOptions);
    }

    // Option 2 : Confettis avancés avec plusieurs effets
    function confetti(options = {}) {
        if (typeof window.confetti !== 'function') {
            console.warn('La bibliothèque canvas-confetti n\'est pas chargée');
            return;
        }

        // Effets de confettis personnalisés
        const effects = {
            basic: () => {
                window.confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: {
                        y: 0.6
                    }
                });
            },
            explosion: () => {
                const duration = 5 * 1000;
                const animationEnd = Date.now() + duration;
                const defaults = {
                    startVelocity: 30,
                    spread: 360,
                    ticks: 60,
                    zIndex: 0
                };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                const interval = setInterval(() => {
                    const timeLeft = animationEnd - Date.now();

                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }

                    const particleCount = 50 * (timeLeft / duration);
                    window.confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.1, 0.3),
                            y: Math.random() - 0.5
                        }
                    }));
                    window.confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.7, 0.9),
                            y: Math.random() - 0.5
                        }
                    }));
                }, 250);
            },
            school: () => {
                const end = Date.now() + (5 * 1000);

                const colors = ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42'];

                (function frame() {
                    window.confetti({
                        particleCount: 2,
                        angle: 60,
                        spread: 55,
                        origin: {
                            x: 0
                        },
                        colors: colors
                    });
                    window.confetti({
                        particleCount: 2,
                        angle: 120,
                        spread: 55,
                        origin: {
                            x: 1
                        },
                        colors: colors
                    });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
            }
        };

        // Type d'effet
        const effectType = options.type || 'basic';

        // Exécuter l'effet
        if (effects[effectType]) {
            effects[effectType]();
        } else {
            effects.basic();
        }
    }

    // Option 3 : Confettis complets avec API complète
    function confetti(config = {}) {
        const defaultOptions = {
            type: 'basic', // Type d'effet : basic, explosion, school
            particleCount: 100, // Nombre de confettis
            spread: 70, // Angle de dispersion
            duration: 3000, // Durée en millisecondes
            origin: {
                y: 0.6
            }, // Point de départ
            colors: [
                '#26ccff',
                '#a25afd',
                '#ff5e7e',
                '#88ff5a',
                '#fcff42'
            ]
        };

        const options = {
            ...defaultOptions,
            ...config
        };

        if (typeof window.confetti !== 'function') {
            console.warn('La bibliothèque canvas-confetti n\'est pas chargée');
            return;
        }

        switch (options.type) {
            case 'explosion':
                const end = Date.now() + options.duration;
                const interval = setInterval(() => {
                    if (Date.now() > end) {
                        return clearInterval(interval);
                    }
                    window.confetti({
                        particleCount: options.particleCount,
                        angle: 60,
                        spread: options.spread,
                        origin: options.origin,
                        colors: options.colors
                    });
                }, 200);
                break;

            case 'school':
                const schoolEnd = Date.now() + options.duration;
                (function frame() {
                    window.confetti({
                        particleCount: 2,
                        angle: 60,
                        spread: options.spread,
                        origin: {
                            x: 0
                        },
                        colors: options.colors
                    });
                    window.confetti({
                        particleCount: 2,
                        angle: 120,
                        spread: options.spread,
                        origin: {
                            x: 1
                        },
                        colors: options.colors
                    });

                    if (Date.now() < schoolEnd) {
                        requestAnimationFrame(frame);
                    }
                }());
                break;

            default:
                window.confetti(options);
        }
    }
});