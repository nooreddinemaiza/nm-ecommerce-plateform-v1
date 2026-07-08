<?php

namespace Src\Helpers;

use Src\Helpers\Config;
use Src\Libraries\Mailer\src\SMTP;
use Src\Libraries\Mailer\src\Exception;
use Src\Libraries\Mailer\src\PHPMailer;


class Mailer
{
    private $mailer;
    private $host;
    private $username;
    private $password;
    private $port;
    private $encryption;
    private $fromEmail;
    private $fromName;

    /**
     * Constructeur de la classe Mailer
     * 
     * @param string $host Hôte SMTP
     * @param string $username Nom d'utilisateur pour l'authentification SMTP
     * @param string $password Mot de passe pour l'authentification SMTP
     * @param int $port Port SMTP (par défaut: 587)
     * @param string $encryption Type de chiffrement (tls, ssl)
     * @param string $fromEmail Adresse email d'expédition par défaut
     * @param string $fromName Nom d'expéditeur par défaut
     */
    public function __construct(
        ?string $host = null,
        ?string $username = null,
        ?string $password = null,
        ?int $port = null,
        ?string $encryption = null,
        ?string $fromEmail = null,
        ?string $fromName = null
    ) {
        $this->host = $host ?? Config::get('WEB_EMAIL_HOST');
        $this->username = $username ?? Config::get('WEB_EMAIL');
        $this->password = $password ?? Config::get('WEB_EMAIL_PASSWORD');
        $this->port = $port ?? Config::get('WEB_EMAIL_PORT');
        $this->encryption = $encryption ?? Config::get('WEB_EMAIL_ENCRYPTION');
        $this->fromEmail = $fromEmail ?? Config::get('WEB_EMAIL');
        $this->fromName = $fromName ?? Config::get('WEB_NAME');
        $this->setupMailer();
    }

    /**
     * Configure l'instance PHPMailer
     */
    private function setupMailer()
    {

        $this->mailer = new PHPMailer(true);

        // Configuration du serveur
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->host;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->username;
        $this->mailer->Password = $this->password;
        $this->mailer->Port = $this->port;

        // Chiffrement
        if ($this->encryption === 'tls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($this->encryption === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        // Encodage
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;

        // Expéditeur par défaut si fourni
        if (!empty($this->fromEmail)) {
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
        }
    }

    /**
     * Vérifie si une adresse email est valide selon sa syntaxe
     * 
     * @param string $email Adresse email à vérifier
     * @return bool Vrai si l'adresse email est valide
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Vérifie si le domaine d'une adresse email existe et possède des enregistrements MX
     * 
     * @param string $email Adresse email à vérifier
     * @return bool Vrai si le domaine existe et a des enregistrements MX
     */
    public static function isValidDomain(string $email): bool
    {
        // S'assurer d'abord que l'email est syntaxiquement valide
        if (!self::isValidEmail($email)) {
            return false;
        }

        // Extraire le domaine de l'adresse email
        list(, $domain) = explode('@', $email);

        // Vérifier si le domaine a des enregistrements MX
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Vérifie si une adresse email est temporaire/jetable
     * basé sur une liste partielle de domaines courants
     * 
     * @param string $email Adresse email à vérifier
     * @return bool Vrai si l'email est potentiellement jetable
     */
    public static function isDisposableEmail(string $email): bool
    {
        // Liste partielle de domaines d'emails jetables courants
        $disposableDomains = [
            'yopmail.com',
            'tempmail.com',
            'temp-mail.org',
            'mailinator.com',
            'guerrillamail.com',
            'guerrillamail.info',
            'sharklasers.com',
            'trashmail.com',
            'trashmail.net',
            'temp-mail.ru',
            '10minutemail.com',
            'mailnesia.com',
            'tempr.email',
            'tempinbox.com',
            'throwawaymail.com'
        ];

        if (!self::isValidEmail($email)) {
            return false;
        }

        list(, $domain) = explode('@', $email);

        return in_array(strtolower($domain), $disposableDomains);
    }

    /**
     * Vérifie complètement une adresse email (syntaxe, domaine, email jetable)
     * 
     * @param string $email Adresse email à vérifier
     * @param bool $checkDisposable Vérifier si l'email est jetable
     * @param bool $checkMX Vérifier les enregistrements MX
     * @return array Tableau associatif des résultats de vérification
     */
    public static function validateEmail(string $email, bool $checkDisposable = true, bool $checkMX = true): array
    {
        $results = [
            'valid_syntax' => self::isValidEmail($email),
            'valid_mx' => false,
            'is_disposable' => false,
            'is_valid' => false,
            'message' => ''
        ];

        if (!$results['valid_syntax']) {
            $results['message'] = "L'adresse email a une syntaxe invalide.";
            return $results;
        }

        if ($checkMX) {
            $results['valid_mx'] = self::isValidDomain($email);
            if (!$results['valid_mx']) {
                $results['message'] = "Le domaine de l'email n'a pas d'enregistrements MX valides.";
                return $results;
            }
        }

        if ($checkDisposable) {
            $results['is_disposable'] = self::isDisposableEmail($email);
            if ($results['is_disposable']) {
                $results['message'] = "L'adresse email semble être une adresse jetable/temporaire.";
                return $results;
            }
        }

        $results['is_valid'] = true;
        $results['message'] = "L'adresse email est valide.";

        return $results;
    }

    /**
     * Nettoie une adresse email (supprime les espaces, convertit en minuscules)
     * 
     * @param string $email Adresse email à nettoyer
     * @return string Adresse email nettoyée
     */
    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Vérifie si un email existe réellement (tentative SMTP)
     * Note: Cette méthode peut être bloquée par certains serveurs et n'est pas fiable à 100%
     * 
     * @param string $email Adresse email à vérifier
     * @return bool|null Vrai si l'email existe, faux s'il n'existe pas, null si indéterminé
     */
    public static function verifyEmailExists(string $email): ?bool
    {
        if (!self::isValidEmail($email) || !self::isValidDomain($email)) {
            return false;
        }

        list(, $domain) = explode('@', $email);

        // Obtenir les enregistrements MX du domaine
        $mxhosts = [];
        if (!getmxrr($domain, $mxhosts)) {
            return false;
        }

        // Utiliser le premier serveur MX
        $host = $mxhosts[0];

        // Tenter une connexion SMTP
        $conn = @fsockopen($host, 25, $errno, $errstr, 5);
        if (!$conn) {
            return null; // Impossible de se connecter, résultat indéterminé
        }

        // Attendre la réponse du serveur
        $response = fgets($conn, 1024);
        if (empty($response)) {
            @fclose($conn);
            return null;
        }

        // Dire bonjour au serveur
        $cmds = [
            "HELO " . $_SERVER['SERVER_NAME'] . "\r\n",
            "MAIL FROM: <verify@" . $_SERVER['SERVER_NAME'] . ">\r\n",
            "RCPT TO: <{$email}>\r\n",
            "QUIT\r\n"
        ];

        $exists = false;

        foreach ($cmds as $cmd) {
            fputs($conn, $cmd);
            $response = fgets($conn, 1024);

            // Si la commande RCPT TO renvoie un code 250, l'email existe
            if (strpos($cmd, 'RCPT TO:') === 0) {
                $exists = (strpos($response, '250') === 0);
            }
        }

        @fclose($conn);
        return $exists;
    }

    /**
     * Définit l'expéditeur de l'email après validation
     * 
     * @param string $email Adresse email de l'expéditeur
     * @param string $name Nom de l'expéditeur
     * @return Mailer L'instance courante pour chaînage
     * @throws Exception Si l'email est invalide
     */
    public function setFrom(string $email, string $name = '')
    {
        // Validation de l'email
        if (!self::isValidEmail($email)) {
            throw new Exception("L'adresse email d'expéditeur '{$email}' est invalide.");
        }

        $this->mailer->setFrom($email, $name);
        return $this;
    }

    /**
     * Ajoute un destinataire après validation
     * 
     * @param string $email Adresse email du destinataire
     * @param string $name Nom du destinataire
     * @param bool $validate Valider l'email avant de l'ajouter
     * @return Mailer L'instance courante pour chaînage
     * @throws Exception Si l'email est invalide
     */
    public function addRecipient(string $email, string $name = '', bool $validate = true)
    {
        // Nettoyage et validation optionnelle de l'email
        $email = self::sanitizeEmail($email);

        if ($validate && !self::isValidEmail($email)) {
            throw new Exception("L'adresse email du destinataire '{$email}' est invalide.");
        }

        $this->mailer->addAddress($email, $name);
        return $this;
    }

    /**
     * Ajoute un destinataire en copie (CC)
     * 
     * @param string $email Adresse email du destinataire en copie
     * @param string $name Nom du destinataire en copie
     * @param bool $validate Valider l'email avant de l'ajouter
     * @return Mailer L'instance courante pour chaînage
     * @throws Exception Si l'email est invalide
     */
    public function addCC(string $email, string $name = '', bool $validate = true)
    {
        // Nettoyage et validation optionnelle de l'email
        $email = self::sanitizeEmail($email);

        if ($validate && !self::isValidEmail($email)) {
            throw new Exception("L'adresse email CC '{$email}' est invalide.");
        }

        $this->mailer->addCC($email, $name);
        return $this;
    }

    /**
     * Ajoute un destinataire en copie cachée (BCC)
     * 
     * @param string $email Adresse email du destinataire en copie cachée
     * @param string $name Nom du destinataire en copie cachée
     * @param bool $validate Valider l'email avant de l'ajouter
     * @return Mailer L'instance courante pour chaînage
     * @throws Exception Si l'email est invalide
     */
    public function addBCC(string $email, string $name = '', bool $validate = true)
    {
        // Nettoyage et validation optionnelle de l'email
        $email = self::sanitizeEmail($email);

        if ($validate && !self::isValidEmail($email)) {
            throw new Exception("L'adresse email BCC '{$email}' est invalide.");
        }

        $this->mailer->addBCC($email, $name);
        return $this;
    }

    /**
     * Définit le sujet de l'email
     * 
     * @param string $subject Sujet de l'email
     * @return Mailer L'instance courante pour chaînage
     */
    public function setSubject(string $subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * Définit le corps de l'email en texte brut
     * 
     * @param string $body Corps de l'email en texte brut
     * @return Mailer L'instance courante pour chaînage
     */
    public function setTextBody(string $body)
    {
        $this->mailer->isHTML(false);
        $this->mailer->Body = $body;
        return $this;
    }

    /**
     * Définit le corps de l'email en HTML
     * 
     * @param string $htmlBody Corps de l'email en HTML
     * @param string $altBody Alternative en texte brut (facultatif)
     * @return Mailer L'instance courante pour chaînage
     */
    public function setHtmlBody(string $htmlBody, string $altBody = '')
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $htmlBody;

        if (!empty($altBody)) {
            $this->mailer->AltBody = $altBody;
        }

        return $this;
    }

    /**
     * Ajoute une pièce jointe
     * 
     * @param string $path Chemin du fichier à joindre
     * @param string $name Nom à afficher pour la pièce jointe (facultatif)
     * @return Mailer L'instance courante pour chaînage
     * @throws Exception Si le fichier n'existe pas
     */
    public function addAttachment(string $path, string $name = '')
    {
        if (!file_exists($path)) {
            throw new Exception("Le fichier '$path' n'existe pas");
        }

        $this->mailer->addAttachment($path, $name);
        return $this;
    }

    /**
     * Envoie l'email configuré
     * 
     * @return bool Vrai si l'envoi a réussi
     * @throws Exception En cas d'erreur d'envoi
     */
    public function send()
    {
        try {
            return $this->mailer->send();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'envoi de l'email: " . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Réinitialise tous les destinataires et pièces jointes
     * 
     * @return Mailer L'instance courante pour chaînage
     */
    public function reset()
    {
        $this->mailer->clearAddresses();
        $this->mailer->clearCCs();
        $this->mailer->clearBCCs();
        $this->mailer->clearAttachments();
        $this->mailer->Subject = '';
        $this->mailer->Body = '';
        $this->mailer->AltBody = '';

        return $this;
    }

    /**
     * Active ou désactive le mode débogage
     * 
     * @param bool $enable Activer (true) ou désactiver (false) le débogage
     * @param int $level Niveau de détail du débogage (SMTP::DEBUG_*)
     * @return Mailer L'instance courante pour chaînage
     */
    public function setDebug(bool $enable, int $level = SMTP::DEBUG_SERVER)
    {
        $this->mailer->SMTPDebug = $enable ? $level : SMTP::DEBUG_OFF;
        return $this;
    }

    /**
     * Méthode utilitaire pour envoyer un email rapidement avec validation d'email
     * 
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $body Corps de l'email
     * @param bool $isHtml Indique si le corps est en HTML
     * @param array $attachments Tableau de chemins de fichiers à joindre
     * @param bool $validateEmail Valider l'email du destinataire
     * @return bool Vrai si l'envoi a réussi
     * @throws Exception En cas d'erreur d'envoi ou d'email invalide
     */
    public function quickSend(
        string $to,
        string $subject,
        string $body,
        bool $isHtml = true,
        array $attachments = [],
        bool $validateEmail = true
    ) {
        $this->reset();

        // Validation de l'email si demandée
        if ($validateEmail) {
            $to = self::sanitizeEmail($to);
            if (!self::isValidEmail($to)) {
                throw new Exception("L'adresse email du destinataire '{$to}' est invalide.");
            }
        }

        $this->addRecipient($to, '', false); // False car déjà validé si nécessaire
        $this->setSubject($subject);

        if ($isHtml) {
            $this->setHtmlBody($body);
        } else {
            $this->setTextBody($body);
        }

        foreach ($attachments as $path) {
            $this->addAttachment($path);
        }

        return $this->send();
    }

    /**
     * Génère une version texte à partir d'un contenu HTML
     * 
     * @param string $html Contenu HTML
     * @return string Version texte du contenu HTML
     */
    public static function htmlToText(string $html): string
    {
        // Supprimer les balises HTML
        $text = strip_tags($html);

        // Convertir les entités HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Supprimer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);

        // Convertir les sauts de ligne
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);

        return trim($text);
    }
}
