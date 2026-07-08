<?php

namespace Src\Controllers;

use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Models\Visitor;
use Src\Helpers\EmailSpam;
use Src\Helpers\SessionManager;
use Src\Services\CaptchaGenerator;

class VisitorController
{
    private $visitorModel;
    private $captchaHandler;
    private $sessionManager;
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        $this->visitorModel = new Visitor();
        $this->captchaHandler = new CaptchaGenerator(Config::get("CAPTCHA_LENGTH"));
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
        AppLog::error("Impossible de charger le CAPTCHA dans la page de contact");
    }
    public function captchaRenew()
    {
        $this->captchaHandler->generateText();
        $imageData = $this->captchaHandler->createImage();
        header('Content-Type: application/json');
        echo json_encode(['imageData' => $imageData]);
        exit;
    }
    private function captchaScript()
    {
        return <<<HTML
                <script>
                    document.getElementById('captcha-validate').addEventListener('click', function() {
                        const btnV = document.getElementById('captcha-validate');
                        btnV.setAttribute("disabled",true);
                        const input = document.getElementById('captcha-input');
                        const value = input.value.trim();
                        const csrf_token = document.getElementById('csrf_token').value.trim();
                        const name = document.getElementById('name').value.trim();
                        const email = document.getElementById('email').value.trim();
                        const subject = document.getElementById('subject').value.trim();
                        const message = document.getElementById('message').value.trim();
                        if (!value) {
                            showToaster('Veuillez entrer le texte de l\'image');
                            return;
                        }
                        // Montrer un indicateur de chargement
                        const button = this;
                        const originalText = button.innerHTML;
                        button.disabled = true;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';
                        
                        fetch('/contact/check-captcha', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                captcha: value,
                                csrf_token: csrf_token,
                                name : name,
                                email : email,
                                subject : subject,
                                message : message,
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToaster(data.message);
                                document.getElementById("contact-us-area").innerHTML = `
                                    <div class="alert alert-success" role="alert">
                                        <h4 class="alert-heading">Message</h4>
                                        <p>`+ data.message +`</p>
                                        <p>Merci pour votre message ! Vous serez redirigé vers l'accueil dans quelques instants. </p>
                                    </div>
                                `;
                                document.getElementById('contact-us-area').scrollIntoView({ behavior: 'smooth' });
                                setTimeout(() => {
                                    window.location.href = "/";
                                }, 5000);
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
                            btnV.setAttribute("disabled",false);
                            // Restaurer le bouton
                            button.disabled = false;
                            button.innerHTML = originalText;
                        });
                    });
                    // Fonction pour régénérer uniquement l'image CAPTCHA sans recharger la page
                    function regenerateCaptchaImage() {
                        fetch('/contact/regenerate-captcha', {
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
    public function sendMessage()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        foreach ($data as $key => $value) {
            $data[$key] = trim(htmlspecialchars($value));
        }
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['sent_at'] = date('Y-m-d H:i:s');
        $response = [];
        if (!isset($data['csrf_token']) || $data['csrf_token'] != $this->sessionManager->getCsrfToken()) {
            echo json_encode([
                'success'  => false,
                'message' => 'CSRF token pas valide',
            ]);
            AppLog::warning("Invalid CSRF token pour IP: $data[ip_address], $_SERVER[HTTP_USER_AGENT]");
            exit;
        }
        $result =  $this->captchaHandler->handleCaptchaVerification($data["captcha"]);
        if ($result['success']) {
            unset($data['captcha']);
            $allowed = $this->visitorModel->allowedToSendMessage($data['ip_address'], $data['user_agent'], $data['sent_at']);
            if (!$allowed) {
                echo json_encode([
                    'success'  => false,
                    'message' => 'Vous avez déjà envoyé un message, veuillez patienter avant de pouvoir envoyer un nouveau!',
                ]);
                exit;
            } else {
                if (strlen($data['name']) < 3 || empty($data['email']) || strlen($data['message']) < 10) {
                    $response['success'] = false;
                    $response['message'] = 'Veuillez remplir les champs requis!';
                }
                if (EmailSpam::checkSpamEmail($data['email'])) {
                    echo json_encode([
                        'success'  => false,
                        'message' => "Adresse email invalid",
                    ]);
                    exit;
                }
                $status = $this->visitorModel->sendMessage($data);
                if ($status) {
                    echo json_encode([
                        'success'  => true,
                        'message' => 'Message envoyé avec succès!',
                    ]);
                    exit;
                } else {
                    echo json_encode([
                        'success'  => false,
                        'message' => 'Une erreur est survenue lors de l\'envoi du message!'
                    ]);
                    exit;
                }
            }
        } else {
            echo json_encode($result);
            exit;
        }
    }

    public function latestMessages()
    {
        $messages = $this->visitorModel->latestMessages();
        return $messages;
    }
    public function getMessages()
    {
        $messages = $this->visitorModel->getMessages();
        return $messages;
    }
    public function getPaginatedMessages(): void
    {
        $data = $_POST;
        foreach ($data as $key => $value) {
            $$key = trim(htmlspecialchars($value));
        }

        if (!isset($newOnly) || !isset($search) || !isset($page) || !isset($per_page)) {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            exit;
        }

        $page = intval($page);
        $per_page = intval($per_page);
        $newOnly = intval($newOnly) === 1;

        $messages = $this->visitorModel->getPaginatedMessages($page, $per_page, $search, $newOnly);
        echo json_encode(["success" => true, "data" => $messages]);
    }
    public function deleteMessage(): void
    {
        $data = $_POST;
        foreach ($data as $key => $value) {
            $$key = trim(htmlspecialchars($value));
        }
        $status = $this->visitorModel->deleteMessage($feedback_id);
        if ($status) {
            echo json_encode(["success" => true, "message" => "Message supprimé avec succès!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
        }
    }
}
