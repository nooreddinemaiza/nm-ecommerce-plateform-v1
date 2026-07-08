<?php

namespace Src\Middlewares;

use Src\Controllers\OrderController;
use Src\Helpers\Config;
use Src\Services\CaptchaGenerator;
use Src\Services\Route;

class OrderConsulterMiddleware
{
    public static $captcha;

    // Initialisation du captcha
    public static function init()
    {
        self::$captcha = new CaptchaGenerator(Config::get("CAPTCHA_LENGTH"));
    }

    // Vérifie si la commande existe
    public static function checkOrderExists($reference, $needle)
    {

        if (!isset($_SESSION['order_consult'])) {
            $_SESSION['order_consult'] = 1;
        }
        if ($_SESSION['order_consult'] > 3) {
            return [];
        }
        $_SESSION['order_consult']++;
        if (!is_numeric($reference)) {
            return [];
        }
        $order = (new OrderController)->find($reference, $needle);
        if (!$order) {
            return [];
        }
        $_SESSION['order_infos'] = $order;
        return $order;
    }

    // Affiche le CAPTCHA
    public static function orderCaptchaSet()
    {
        self::init();
        return (self::$captcha->generateHtml() . self::captchaScript());
    }

    // Régénère le CAPTCHA
    public static function captchaRenew()
    {
        self::init();
        self::$captcha->generateText();
        $imageData = self::$captcha->createImage();
        header('Content-Type: application/json');
        echo json_encode(['imageData' => $imageData]);
        exit;
    }
    public static function checkCaptcha()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['captcha'])) {
            $captchaPost = htmlentities(trim($data["captcha"]));
            self::init();
            $result = self::$captcha->handleCaptchaVerification($captchaPost);
            if ($result["success"]) {
                $_SESSION["not_robot"]["order"] = true;
                echo json_encode([
                    "success" => true
                ]);
            } else {
                echo json_encode($result);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Veuillez entrer le texte du CAPTCHA.',
            ]);
            exit;
        }
    }
    // Génère le script pour le CAPTCHA
    public static function captchaScript()
    {
        return <<<HTML
            <script>
                document.getElementById('captcha-validate').addEventListener('click', function() {
                    const btnV = document.getElementById('captcha-validate');
                    const input = document.getElementById('captcha-input');
                    const value = input.value.trim();
                    if (!value) {
                        showToaster('Veuillez entrer le texte de l\'image');
                        return;
                    }

                    // Montrer un indicateur de chargement
                    const button = this;
                    const originalText = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';

                    fetch('/consulter-commande/captcha-check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            captcha: value,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToaster(data.message);
                            window.location.href = '/consulter-commande';
                        } else {
                            showToaster(data.message || 'Code incorrect, veuillez réessayer.');

                            // Mettre à jour le compteur de tentatives
                            if (data.attempts && data.maxAttempts) {
                                document.getElementById('attempts-counter').textContent = 
                                    `Tentative: ` + data.attempts + ` / ` + data.maxAttempts;

                                // Si nombre max de tentatives atteint, rediriger vers l'accueil
                                if (data.attempts >= data.maxAttempts) {
                                    setTimeout(() => {
                                        window.location.href = '/'; // Redirection vers la page d'accueil
                                    }, 2000);
                                }
                            }

                            // Effacer le champ d'entrée pour une nouvelle tentative
                            input.value = '';
                            input.focus();
                        }
                    })
                    .catch(error => {
                        showToaster('Erreur de connexion. Veuillez réessayer.');
                    })
                    .finally(() => {
                        btnV.setAttribute("disabled", false);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
                });

                // Validation au clic sur Entrée
                document.getElementById('captcha-input').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.getElementById('captcha-validate').click();
                    }
                });
            </script>
        HTML;
    }
}
