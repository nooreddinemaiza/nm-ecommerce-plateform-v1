<?php

namespace Src\Services;

use Src\Helpers\Config;

/**
 * Classe CaptchaGenerator
 * Génère un CAPTCHA textuel sous forme d'image et gère la validation
 */
class CaptchaGenerator
{
    private $text;
    private $length;
    private $session;

    /**
     * Constructeur
     * @param int $length Longueur maximale du texte du CAPTCHA
     */
    public function __construct($length = 6)
    {
        $this->length = $length ?? 6;
        $this->session = &$_SESSION;
        if (empty($this->session['captcha_text'])) {
            $this->generateText();
        } else {
            $this->text = $_SESSION['captcha_text'];
        }
    }

    /**
     * Génère un texte aléatoire pour le CAPTCHA
     */
    public function generateText()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $text = '';

        $charsLength = strlen($chars) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $text .= $chars[rand(0, $charsLength)];
        }

        $this->text = $text;
        $this->storeInSession();
    }

    /**
     * Stocke le texte du CAPTCHA dans la session
     */
    private function storeInSession()
    {
        $this->session['captcha_text'] = $this->text;
    }

    /**
     * Crée l'image du CAPTCHA
     * @return string Chemin vers l'image temporaire ou données base64
     */
    public function createImage()
    {
        // Paramètres de l'image
        $font = 5; // Police intégrée
        $width = imagefontwidth($font) * strlen($this->text) + 20;
        $height = imagefontheight($font) + 10;

        // Création de l'image
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 30, 30, 30);
        $noiseColor = imagecolorallocate($image, 100, 120, 180);

        // Remplir le fond
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Ajouter du bruit
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }

        // Ajouter des lignes
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noiseColor);
        }

        // Écrire le texte
        imagestring($image, $font, 10, ($height - imagefontheight($font)) / 2, $this->text, $textColor);

        // Démarrer la mise en mémoire tampon
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
    
        // Retourner l'image en base64
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    public function generateHtml($message = "Veuillez entrer le texte de l'image", $maxAttempts = 3)
    {
        $imageData = $this->createImage();
        $html = <<<HTML
            <div class="captcha-container card border-secondary mb-4" style="max-width: 320px;">
            <div class="card-header bg-light">
                <p class="text-muted mb-0"><i class="fas fa-shield-alt me-2"></i>{$message}</p>
            </div>
            <div class="card-body">
                <div class="captcha-image text-center mb-3">
                    <img src="{$imageData}" alt="CAPTCHA" class="img-fluid border rounded shadow-sm" />
                </div>
                <div class="captcha-form">
                    <div class="input-group">
                        <input type="text" id="captcha-input" class="form-control" placeholder="Entrez le texte ci-dessus" autocomplete="off" aria-label="Captcha" />
                        <button type="button" id="captcha-validate" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Valider
                        </button>
                    </div>
                    <div id="attempts-info" class="text-muted small mt-2">
                        <span id="attempts-counter">Tentative: 1/{$maxAttempts}</span>
                    </div>
                </div>
            </div>
        </div>
        HTML;
        return $html;
    }

    /**
     * Valide le CAPTCHA
     * @param string $input Texte entré par l'utilisateur
     * @return bool Vrai si la validation réussit, faux sinon
     */
    public function validate($input)
    {
        if (!isset($this->session['captcha_text'])) {
            return false;
        }
        $result = $input === $this->session['captcha_text'];
        // Effacer le CAPTCHA après vérification pour empêcher la réutilisation
        if ($result) {
            unset($this->session['captcha_text']);
        }

        return $result;
    }

    function handleCaptchaVerification($captcha)
    {
        $captchaInput = $captcha;

        // Définir le nombre maximum de tentatives
        $maxAttempts = Config::get("CAPTCHA_MAX_ATTEMPTS");

        // Initialiser le compteur de tentatives s'il n'existe pas
        if (!isset($_SESSION['captcha_attempts'])) {
            $_SESSION['captcha_attempts'] = 0;
        }

        // Vérifier si l'utilisateur a dépassé le nombre maximum de tentatives
        if ($_SESSION['captcha_attempts'] >= $maxAttempts) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Trop de tentatives. Vous allez être redirigé vers la page d\'accueil.',
                'attempts' => $_SESSION['captcha_attempts'],
                'maxAttempts' => $maxAttempts
            ]);
            exit;
        }
        // Instancier le CAPTCHA et valider l'entrée
        $isValid = $this->validate($captchaInput);

        if (!$isValid) {
            // Incrémenter le compteur de tentatives
            $_SESSION['captcha_attempts']++;
        } else {
            // Réinitialiser le compteur en cas de succès
            $_SESSION['captcha_attempts'] = 0;
        }

        // Préparer la réponse
        $response = [
            'success' => $isValid,
            'attempts' => $_SESSION['captcha_attempts'],
            'maxAttempts' => $maxAttempts
        ];

        // Ajouter un message personnalisé en fonction du résultat
        if (!$isValid) {
            if ($_SESSION['captcha_attempts'] >= $maxAttempts) {
                $response['message'] = 'Nombre maximum de tentatives atteint. Vous allez être redirigé.';
            } else {
                $tentativesRestantes = $maxAttempts - $_SESSION['captcha_attempts'];
                $response['message'] = 'Code incorrect. Il vous reste ' . $tentativesRestantes . ' tentative(s).';
            }
        }
        return $response;
    }
}
