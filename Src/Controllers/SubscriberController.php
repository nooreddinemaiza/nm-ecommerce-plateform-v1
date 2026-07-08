<?php

namespace Src\Controllers;

use Src\Helpers\AppLog;
use Src\Helpers\Mailer;
use Src\Helpers\EmailSpam;
use Src\Models\Subscriber;
use Src\Controllers\ProductController;
use Src\Helpers\Config;
use Src\Helpers\SessionManager;
use Src\Services\CaptchaGenerator;

class SubscriberController
{
    private $email;
    private $subscriberModel;
    private $captchaHandler;
    private $sessionManager;
    public function __construct(SessionManager $sessionManager)
    {
        $this->email = Config::get("WEB_EMAIL");
        $this->subscriberModel = new Subscriber();
        $this->captchaHandler = new CaptchaGenerator();
        $this->sessionManager = $sessionManager;
    }

    /**
     * Vérifie si un email est potentiellement un spam ou une entrée aléatoire
     * 
     * @param string $email L'adresse email à vérifier
     * @return bool true si c'est un spam, false sinon
     */
    private function checkSpamEmail($email)
    {
        // Validation syntaxique de base avec filtre PHP
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            AppLog::warning("Email invalide: $email");
            return true; // Email invalide syntaxiquement [[1]](#__1)
        }

        // Vérification des domaines temporaires/jetables
        $tempDomains = [
            'mailinator.com',
            'temp-mail.org',
            'guerrillamail.com',
            'jetable.org',
            'yopmail.com',
            'throwawaymail.com'
        ]; // [[2]](#__2)

        $domain = strtolower(substr(strrchr($email, "@"), 1));
        if (in_array($domain, $tempDomains)) {
            AppLog::warning("domaine invalide: $domain");
            return true;
        }
        $name = strstr($email, '@', true); // Prend tout avant @
        // Analyse des patterns suspects
        $suspiciousPatterns = [
            '/\d{4,}@/',              // Nombreux chiffres consécutifs
            '/[^a-z0-9.-]+/i',        // Caractères spéciaux inhabituels [[3]](#__3)
            '/[0-9]{8,}/',            // Trop de chiffres consécutifs
            '/(.)\1{4,}/',            // Caractères répétés plus de 4 fois
            '/(test|spam|fake|temp)/', // Mots suspects courants
            '/^[0-9]+[a-z]+@/',       // Commence par des chiffres suivis de lettres
            '/[a-z]{15,}/'            // Trop de lettres consécutives
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, strtolower($name))) {
                AppLog::warning("Pattern suspect: $pattern");
                return true;
            }
        }

        // Vérification des TLDs valides
        $validTlds = [
            'com',
            'org',
            'net',
            'edu',
            'gov',
            'mil',
            'io',
            'co',
            'info',
            'biz',
            'fr',
            'de',
            'uk',
            'ma',
            'ca'
        ]; // [[4]](#__4)

        $tldParts = explode('.', $domain);
        $tld = strtolower(end($tldParts));

        if (!in_array($tld, $validTlds)) {
            return true;
        }

        // Vérification de l'entropie du domaine
        $entropy = $this->calculateEntropy($domain);
        if ($entropy > 3.5) { // Seuil d'entropie ajusté
            AppLog::warning("Entropie élevée: $entropy");
            return true;
        }

        // Vérification MX record (optionnel mais recommandé)
        if (!checkdnsrr($domain, 'MX')) {
            return true; // Domaine sans enregistrement MX [[5]](#__5)
        }

        return false;
    }

    /**
     * Calcule l'entropie d'une chaîne pour détecter le caractère aléatoire
     */
    private function calculateEntropy($string)
    {
        $length = strlen($string);
        $frequencies = array_count_values(str_split($string));

        $entropy = 0;
        foreach ($frequencies as $freq) {
            $probability = $freq / $length;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    public function subscribe()
    {
        $data = $_POST;
        // Sécuriser les entrées
        foreach ($data as $key => $value) {
            $data[$key] = trim(htmlspecialchars($value));
        }

        // Validation de l'email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Veuillez entrer un email valide!"]);
            return;
        }

        // Vérifier si l'email est un spam
        if ($this->checkSpamEmail($data['email'])) {
            echo json_encode(["success" => false, "message" => "Cet email semble être une adresse temporaire ou invalide!"]);
            return;
        }

        // Informations supplémentaires (IP, navigateur, timestamp)
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['subscribed_at'] = date('Y-m-d H:i:s');
        $alreadySub = $this->subscriberModel->getSubscriber($data['email']);
        if ($alreadySub) {
            echo json_encode(["success" => false, "message" => "Vous êtes déjà inscrit!"]);
            return;
        }
        // Vérifier les restrictions d'inscription
        $result = $this->subscriberModel->canSubscribe($data);
        if ($result['alreadySubscribed']) {
            echo json_encode(["success" => false, "message" => "Vous êtes déjà inscrit!"]);
            return;
        }

        if (!$result['allowedToSubscribe']) {
            echo json_encode(["success" => false, "message" => "Vous avez envoyé trop de requêtes."]);
            return;
        }

        // Enregistrer l'inscription
        if ($this->subscriberModel->subscribe($data)) {
            echo json_encode(["success" => true, "message" => "Inscription réussie!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
        }
    }
    public function listSubscribers()
    {
        $result = $this->subscriberModel->listSubscribers();
        if ($result) {
            $result = array_map(function ($item) {
                unset($item['ip_address'], $item['user_agent']);
                return $item;
            }, $result);
            echo json_encode([
                'success' => true,
                'data'    => $result
            ]);
            exit;
        }
        echo json_encode([
            'success' => false,
            'data'    => []
        ]);
    }
    //fonction de suppression es abonnées
    public function delete()
    {
        $ids = null;
        if (is_array($_POST['id'])) {
            if (!count($_POST['id'])) {
                echo json_encode([
                    "success" => false,
                    "message" => "Erreur lors de la récuperation des données!"
                ]);
                return;
            } else {
                $ids = implode(',', $_POST['id']);
            }
        } else {
            if (empty($_POST['id'])) {
                echo json_encode([
                    "success" => false,
                    "message" => "Erreur lors de la récuperation des données!"
                ]);
                return;
            } else $ids = $_POST['id'];
        }
        $result = $this->subscriberModel->deleteSubscriber($ids);
        if ($result) {
            echo json_encode([
                "success" => true,
                "message" => "Suppression avec succé!"
            ]);
            return;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue lors de la suppression!"
            ]);
            return;
        }
    }
    public function getSubscriptionStats()
    {
        return $this->subscriberModel->getSubscriptionStats();
    }

    public function notifySubscribers()
    {
        // Sécuriser l'entrée `message`
        $message = trim(htmlspecialchars($_POST['message'] ?? ''));

        if (empty($message)) {
            echo json_encode(["success" => false, "message" => "Veuillez saisir un message avant d'envoyer la notification."]);
            return;
        }

        // Récupérer les emails sélectionnés ou tous les abonnés si aucun email spécifique n'est fourni
        $emails = $_POST['emails'] ?? null;
        $subscribers = is_array($emails) && count($emails) > 0
            ? $emails
            : $this->subscriberModel->getSubscribers();

        if (empty($subscribers)) {
            echo json_encode(["success" => false, "message" => "Aucun abonné sélectionné."]);
            return;
        }

        // Envoyer les notifications
        $countSuccess = 0;
        foreach ($subscribers as $subscriber) {
            if ($this->sendNotificationToClient($subscriber, $message)) {
                $countSuccess++;
            }
        }

        // Retourner le résultat
        echo json_encode([
            'success' => $countSuccess > 0,
            'message' => $countSuccess > 0
                ? "Notification envoyée avec succès à $countSuccess / " . count($subscribers)
                : "Erreur lors de l'envoi !"
        ]);
    }
    /**
     * Envoie une notification personnalisée au client
     * 
     * @param array $client_info Les informations du client
     * @param string $message Le contenu de la notification
     * @param string $subject Le sujet de l'email (optionnel)
     * @return bool Succès ou échec de l'envoi
     */
    private function sendNotificationToClient($client_info, $message, $subject = 'Notification importante')
    {
        $to = $client_info['email'] ?? $this->email; // Email du client ou valeur par défaut
        $name = 'Cher client';

        // Style CSS inline pour l'email
        $css = '
        body { font-family: "Segoe UI", Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4A6FDC; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .header h2 { margin: 0; font-weight: 500; }
        .content { background-color: #fff; padding: 20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; margin-bottom: 15px; color: #2C3E50; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .message-content { background-color: #f9f9f9; padding: 15px; border-radius: 6px; line-height: 1.8; }
        .cta-button { display: inline-block; background-color: #4A6FDC; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 500; margin-top: 20px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #777; text-align: center; }
        .contact-info { margin-top: 15px; background-color: #f9f9f9; padding: 10px; border-radius: 6px; font-size: 14px; }
        ';

        // Construire le corps de l'email en HTML
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>📢 ' . htmlspecialchars($subject) . '</h2>
                </div>
                <div class="content">
                    <div class="section">
                        <div class="section-title">Bonjour ' . $name . ',</div>
                        <div class="message-content">' . nl2br(htmlspecialchars($message)) . '</div>';

        // Ajouter un bouton CTA si un identifiant de commande est disponible
        if (isset($client_info['id'])) {
            $htmlBody .= '
                        <div style="text-align: center;">
                            <a href="' . WEB_URL . '/commandes/' . $client_info['id'] . '" class="cta-button">Voir ma commande</a>
                        </div>';
        }

        $htmlBody .= '
                    </div>
                    
                    <div class="contact-info">
                        <strong>Besoin d\'aide?</strong> Notre équipe est disponible pour vous aider :
                        <ul style="margin-top: 5px;">
                            <li>Par téléphone : ' . Config::get("WEB_PHONE") . '</li>
                            <li>Par email : ' . Config::get("$this->email") . '</li>
                        </ul>
                    </div>
                    
                    <div class="footer">
                        <p>Merci de votre confiance.</p>
                        <p>© ' . date('Y') . ' ' . Config::get("WEB_NAME") . '. Tous droits réservés.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Envoi de l'email
        $mailer = new Mailer();
        return $mailer->quickSend($to, $subject, $htmlBody, true);
    }
    public function captchaSet()
    {
        $result = $this->captchaHandler->generateHtml();
        if ($result) {
            $result .= $this->captchaScript();
            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
            exit;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue, Veuillez ressayer plus tard"
            ]);
        }
        AppLog::error("Impossible de charger le CAPTCHA dans la page de devis");
    }
    public function captchaRenew()
    {
        $this->captchaHandler->generateText();
        $imageData = $this->captchaHandler->createImage();
        header('Content-Type: application/json');
        echo json_encode(['imageData' => $imageData]);
        exit;
    }
    public function setDevis()
    {
        header('Content-Type: application/json');
        $devisData = json_decode(file_get_contents('php://input'), true);
        foreach ($devisData as $key => $value) {
            if (is_array($value)) {
                $devisData[$key] = array_map(function ($item) {
                    return trim(htmlspecialchars($item));
                }, $value);
            } else {
                $devisData[$key] = trim(htmlspecialchars($value));
            }
        }
        if (!isset($devisData['csrf_token']) || $devisData['csrf_token'] != $this->sessionManager->getCsrfToken()) {
            echo json_encode([
                'success'  => false,
                'message' => 'CSRF token pas valide',
            ]);
            AppLog::warning("Invalid CSRF token pour IP: $devisData[ip_address], $_SERVER[HTTP_USER_AGENT]");
            exit;
        }

        $result =  $this->captchaHandler->handleCaptchaVerification($devisData["captcha"]);
        if ($result["success"]) {
            unset($data['captcha']);
            if (!isset($_SESSION['devis_attempt'])) {
                $_SESSION['devis_attempt'] = 3; // 3 tentatives autorisées
            }
            // 3. Nettoyage et validation des données
            $requiredFields = ['nom', 'email'];
            $data = [];
            $errors = [];

            // Nettoyage des données
            foreach ($devisData as $key => $value) {
                $data[$key] = is_array($value)
                    ? array_map('trim', array_map('htmlspecialchars', $value))
                    : trim(htmlspecialchars($value));
            }

            // Validation des champs obligatoires
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[] = "Le champ $field est obligatoire.";
                }
            }

            // Validation spécifique des champs
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email n'est pas valide.";
            }

            if (!empty($data['telephone']) && !preg_match('/^[0-9+\s]{10,15}$/', $data['telephone'])) {
                $errors[] = "Le numéro de téléphone n'est pas valide.";
            }

            // Validation des produits
            $produits = $data['selectedProducts'] ?? [];
            $notInListdescr = $data['notInListdescr'] ?? '';

            if (empty($produits) && empty($notInListdescr)) {
                $errors[] = "Veuillez sélectionner au moins un produit ou décrire votre besoin.";
            }

            // Limite de produits
            if (count($produits) > 10) {
                $errors[] = "Vous ne pouvez pas sélectionner plus de 10 produits.";
            }

            // Si erreurs, on retourne
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => implode(' ', $errors)
                ]);
                exit;
            }

            // 4. Vérification anti-spam
            if (EmailSpam::checkSpamEmail($data['email'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Adresse email non autorisée."
                ]);
                exit;
            }

            // 5. Vérification abonnement si nécessaire
            if (defined('DEVIS_WITH_SUBSCRIBE') && Config::get("DEVIS_WITH_SUBSCRIBE")) {
                if (!$this->subscriberModel->getSubscriber($data['email'])) {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => "Vous devez être abonné pour faire une demande de devis."
                    ]);
                    exit;
                }
            }

            // 6. Validation des produits
            $productController = new ProductController();
            $produitsExistants = [];

            foreach ($produits as $produitId) {
                if (!ctype_digit($produitId)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Identifiant de produit invalide."
                    ]);
                    exit;
                }

                $produit = $productController->checkDevisPr($produitId);
                if (!$produit) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => "Le produit sélectionné n'existe pas."
                    ]);
                    exit;
                }

                $produitsExistants[] = $produit;
            }

            // 7. Préparation des données du devis
            $devisInfo = [
                'client' => [
                    'nom_prenom' => $data['nom'],
                    'email'      => $data['email'],
                    'phone'      => $data['telephone'] ?? null,
                ],
                'produits' => $produitsExistants,
                'details'  => $data['details'] ?? null,
                'description_perso' => $notInListdescr
            ];

            // 8. Vérification des tentatives
            if ($_SESSION['devis_attempt'] <= 0) {
                http_response_code(429);
                echo json_encode([
                    'success' => false,
                    'message' => "Vous avez atteint le nombre maximal de tentatives. Réessayez plus tard."
                ]);
                exit;
            }

            // 9. Envoi du devis
            try {
                $emailSent = $this->devis($devisInfo);

                if (!$emailSent) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Erreur lors de l'envoi du devis."
                    ]);
                    exit;
                }

                $_SESSION['devis_attempt']--;

                echo json_encode([
                    'success' => true,
                    'message' => "Votre demande de devis a été envoyée avec succès !",
                ]);
            } catch (\Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => "Une erreur est survenue : "
                ]);
                AppLog::warning("(Demande de devis)Email non envoyé :  " . $e->getMessage());
            }
        } else {
            echo json_encode($result);
            exit;
        }
    }
    private function captchaScript()
    {
        return <<<HTML
                <script>
                    document.getElementById('captcha-validate').addEventListener('click', function() {
                        const input = document.getElementById('captcha-input');
                        // Reconstruction des données
                        const value = input.value.trim();
                        const nom = document.getElementById("nom").value.trim();
                        const email = document.getElementById("email").value.trim();
                        const csrf_token = document.getElementById("csrf_token").value.trim();
                        const telephone = document.getElementById("telephone").value.trim();
                        const notInListdescr = document.getElementById("notInListdescr").value.trim();
                        const details = document.getElementById("details").value.trim();
                        
                        // Nettoyage des produits vides
                        const produits = [];
                        $('select[name="produits[]"]').each(function () {
                            const val = $(this).val();
                            if (val) produits.push(val);
                        });
                        let selectedProducts = [];
                        produits.forEach(id => {
                            selectedProducts.push(id);
                        });
                        if (!value) {
                            showToaster('Veuillez entrer le texte de l\'image');
                            return;
                        }
                        // Montrer un indicateur de chargement
                        const button = this;
                        const originalText = button.innerHTML;
                        button.disabled = true;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';
                        
                        fetch('/devis/check-captcha', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                captcha: value,
                                nom: nom,
                                email: email,
                                csrf_token: csrf_token,
                                telephone: telephone,
                                notInListdescr: notInListdescr,
                                details: details,
                                selectedProducts: selectedProducts,
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToaster(data.message);
                                document.getElementById("devis-area").innerHTML = `
                                    <div class="alert alert-success" role="alert">
                                        <h4 class="alert-heading">Message</h4>
                                        <p>`+ data.message +`</p>
                                        <p>
                                         Votre demande de devis a été envoyée avec succès ! 
                                         Nous vous contacterons sous 24h.</p>
                                    </div>
                                `;
                                document.getElementById('devis-area').scrollIntoView({ behavior: 'smooth' });
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
                                    } else {
                                        // Régénérer uniquement l'image CAPTCHA sans recharger la page
                                        regenerateCaptchaImage();
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
                            // Restaurer le bouton
                            button.disabled = false;
                            button.innerHTML = originalText;
                        });
                    });
                    // Fonction pour régénérer uniquement l'image CAPTCHA sans recharger la page
                    function regenerateCaptchaImage() {
                        fetch('/devis/regenerate-captcha', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.imageData) {
                                // Mettre à jour uniquement l'image
                                document.querySelector('.captcha-image img').src = data.imageData;
                            }
                        })
                        .catch(error => {
                            console.error('Erreur de régénération du CAPTCHA:', error);
                        });
                    }
                    // Validation au clic sur Entrée
                    document.getElementById('captcha-input').addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            document.getElementById('captcha-validate').click();
                        }
                    });
                </script>
            HTML;
    }

    private function devis(array $devisInfo): bool
    {
        try {
            $adminEmail = $this->email; // Email de l'administrateur
            $clientName = $devisInfo['client']['nom_prenom'];
            $subject = 'Nouvelle demande de devis - ' . $clientName;

            // Construire le contenu HTML de l'email
            $htmlContent = $this->buildDevisEmailContent($devisInfo);
            // Envoyer l'email
            $mailer = new Mailer();
            $sent = $mailer->quickSend(
                $adminEmail,
                $subject,
                $htmlContent,
                true // HTML content
            );

            // Envoyer une copie au client si nécessaire
            if (defined('DEVIS_SEND_COPY_TO_CLIENT') && Config::get("DEVIS_SEND_COPY_TO_CLIENT")) {
                $clientSubject = 'Votre demande de devis chez ' . WEB_NAME;
                $mailer->quickSend(
                    $devisInfo['client']['email'],
                    $clientSubject,
                    $this->buildClientCopyEmailContent($devisInfo),
                    true
                );
            }

            return $sent;
        } catch (\Exception $e) {
            error_log('Erreur envoi email devis: ' . $e->getMessage());
            return false;
        }
    }

    private function buildDevisEmailContent(array $devisInfo): string
    {
        $client = $devisInfo['client'];
        $produits = $devisInfo['produits'] ?? [];
        $descriptionPerso = $devisInfo['description_perso'] ?? null;
        $details = $devisInfo['details'] ?? null;

        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Demande de Devis</title>
            <style>
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    color: #333;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }

                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }

                .header {
                    background-color: #4A6FDC;
                    color: white;
                    padding: 20px;
                    border-radius: 8px 8px 0 0;
                }

                .header h2 {
                    margin: 0;
                    font-weight: 500;
                }

                .content {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 0 0 8px 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                .section {
                    margin-bottom: 25px;
                }

                .section-title {
                    font-size: 18px;
                    margin-bottom: 15px;
                    color: #2C3E50;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 8px;
                }

                .info-grid {
                    display: grid;
                    grid-template-columns: 120px 1fr;
                    gap: 10px;
                    margin-bottom: 10px;
                }

                .info-label {
                    font-weight: 500;
                    color: #555;
                }

                .product-list {
                    margin-top: 15px;
                }

                .product-item {
                    padding: 12px;
                    margin-bottom: 10px;
                    background-color: #f9f9f9;
                    border-radius: 6px;
                    border-left: 3px solid #4A6FDC;
                }

                .product-title {
                    font-weight: 500;
                    margin-bottom: 5px;
                }

                .product-meta {
                    font-size: 14px;
                    color: #666;
                }

                .details-box {
                    font-style: italic;
                    color: #777;
                    margin-top: 15px;
                    padding: 10px;
                    background-color: #f5f5f5;
                    border-radius: 4px;
                }

                .custom-description {
                    margin-top: 15px;
                    padding: 10px;
                    background-color: #e8f4fd;
                    border-radius: 4px;
                    border-left: 3px solid #2196F3;
                }

                .alert {
                    background-color: #FFF3CD;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin-top: 20px;
                    color: #856404;
                }

                .action-btn {
                    background-color: #4A6FDC;
                    color: white;
                    text-decoration: none;
                    padding: 10px 15px;
                    border-radius: 4px;
                    display: inline-block;
                    margin-top: 15px;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="header">
                    <h2>📋 Nouvelle demande de devis</h2>
                </div>
                <div class="content">
                    <!-- Informations client -->
                    <div class="section">
                        <div class="section-title">👤 Informations client</div>
                        <div class="info-grid">
                            <div class="info-label">Nom</div>
                            <div><?= htmlspecialchars($client['nom_prenom']) ?></div>

                            <div class="info-label">Email</div>
                            <div><?= htmlspecialchars($client['email']) ?></div>

                            <?php if (!empty($client['phone'])): ?>
                                <div class="info-label">Téléphone</div>
                                <div><?= htmlspecialchars($client['phone']) ?></div>
                            <?php endif; ?>

                            <div class="info-label">Date</div>
                            <div><?= date('d/m/Y H:i') ?></div>
                        </div>
                    </div>

                    <!-- Contenu de la demande -->
                    <div class="section">
                        <?php if (!empty($produits)): ?>
                            <div class="section-title">📦 Produits demandés</div>
                            <div class="product-list">
                                <?php foreach ($produits as $produit): ?>
                                    <div class="product-item">
                                        <div class="product-title"><?= htmlspecialchars($produit['title']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($descriptionPerso)): ?>
                            <div class="custom-description">
                                <strong>Description personnalisée:</strong><br>
                                <?= nl2br(htmlspecialchars($descriptionPerso)) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($details)): ?>
                            <div class="details-box">
                                <strong>Détails supplémentaires:</strong><br>
                                <?= nl2br(htmlspecialchars($details)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Appel à action -->
                    <div class="alert">
                        ⚠️ Cette demande nécessite votre réponse dans les 24 heures.
                    </div>
                    <a href="<?= WEB_URL ?>/admin/devis" class="action-btn">Gérer cette demande</a>
                </div>
            </div>
        </body>

        </html>
    <?php
        return ob_get_clean();
    }

    private function buildClientCopyEmailContent(array $devisInfo): string
    {
        $client = $devisInfo['client'];
        $produits = $devisInfo['produits'] ?? [];
        $details = $devisInfo['details'] ?? null;

        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmation de votre demande de devis</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                    background-color: #f8f8f8;
                    padding: 20px;
                }

                .container {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 600px;
                    margin: 0 auto;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                h2 {
                    color: #2c3e50;
                }

                ul {
                    padding-left: 20px;
                }

                .footer {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #777;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <h2>Bonjour <?= htmlspecialchars($client['name'] ?? ''); ?>,</h2>

                <p>Nous avons bien reçu votre demande de devis. Notre équipe va l’examiner et vous recevrez une réponse dans les <strong>prochaines 24 heures</strong>.</p>

                <?php if (!empty($produits)): ?>
                    <p><strong>Produits demandés :</strong></p>
                    <ul>
                        <?php foreach ($produits as $produit): ?>
                            <li><?= htmlspecialchars($produit['title'] ?? 'Produit') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($details): ?>
                    <p><strong>Détails supplémentaires :</strong></p>
                    <p><?= nl2br(htmlspecialchars($details)) ?></p>
                <?php endif; ?>

                <p>Nous vous remercions pour votre intérêt. Si vous avez des questions supplémentaires, n'hésitez pas à nous contacter.</p>

                <div class="footer">
                    Cordialement,<br>
                    <strong>L'équipe Support</strong><br>
                    <?= htmlspecialchars(WEB_NAME) ?>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }
}
