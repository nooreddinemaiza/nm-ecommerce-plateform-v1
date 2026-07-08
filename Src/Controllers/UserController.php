<?php
// Contrôleur pour la gestion des utilisateurs

namespace Src\Controllers;

use DateTime;
use Src\Models\User;
use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Helpers\Mailer;
use Src\Services\Route;
use Src\Helpers\Validator;
use Src\Helpers\ErrorHandler;
use Src\Helpers\CSRFProtection;
use Src\Helpers\SessionManager;
use Src\Services\Authentication;
use Src\Services\Security;

class UserController
{
    private Validator $validator;
    private User $userModel;
    private Authentication $authenticator;
    private SessionManager $sessionManager;
    private ErrorHandler $errorHandler;
    private Mailer $mailer;
    public function __construct(SessionManager $sessionManager)
    {
        $this->authenticator = new Authentication();
        $this->sessionManager = $sessionManager;
        $this->errorHandler = new ErrorHandler;
        $this->userModel = new User($this->sessionManager);
        $this->validator = new Validator();
        $this->mailer = new Mailer();
    }

    // Créer un utilisateur
    public function createUser()
    {

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->logout();
            exit;
        }
        $data = $_POST;
        // Vérification du token CSRF
        if (!$this->validateCsrftoken($data['csrf_token'])) {
            AppLog::error("CSRF token invalid during user creation attempt.");
            echo json_encode(['error' => 'Token CSRF invalide.']);
            return;
        }
        // Validation des données
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['error' => "Erreur : Tous les champs obligatoires doivent être remplis."]);
            return;
        }
        if (!$this->validator->validateUsername($data['username'])) {
            echo json_encode(['error' => $this->validator->errorhandler()->getSampleError('invalidusername')]);
            return;
        }
        if ($this->userModel->userExistsByUsername($data['username'])) {
            echo json_encode(['error' => $this->validator->errorhandler()->getSampleError('usedusername')]);
            return;
        }
        if (!$this->validator->validateEmail($data['email'])) {
            echo json_encode(['error' => $this->validator->errorhandler()->getSampleError('email')]);
            return;
        }
        if ($this->userModel->userExistsByEmail($data['email'])) {
            echo json_encode(['error' => $this->validator->errorhandler()->getSampleError('usedEmail')]);
            return;
        }
        if (!empty($data['phone'])) {
            if ($this->userModel->userExistsByPhone($data['phone'])) {
                echo json_encode(['error' => $this->validator->errorhandler()->getSampleError('usedphone')]);
                return;
            }
        }
        // Hachage du mot de passe
        $data['password'] = Security::hashPassword($data["password"]);
        // Création de l'utilisateur
        unset($data['repeatPassword']);
        unset($data['csrf_token']);
        $userId = $this->userModel->createUser($data);
        if ($userId) {
            unset($data['password']);
            AppLog::info("Utilisateur créé avec succès. ID : $userId");
            $data['id'] = $userId;
            $data['created_at'] = (new DateTime())->format('Y-m-d H:i:s');
            echo json_encode($data);
            return true;
        } else {
            AppLog::error("Erreur lors de la création de l'utilisateur.");
            return false;
        }
    }
    public function updateManagerStatus()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->logout();
            exit;
        }
        if (!isset($_POST['csrf_token']) || !$this->validateCsrftoken($_POST['csrf_token'])) {
            AppLog::error("CSRF token invalid pour l'essais de modification du status de compte : ");
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }
        // Vérification de l'ID utilisateur
        if (!isset($_POST['id']) || !is_numeric($_POST['id']) || $_SESSION['user_id'] == intval($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            return;
        }

        $user = $this->userModel->getUserById($_POST['id']);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            exit;
        }
        if ($user["status"] == $_POST["status"]) {
            echo json_encode(['success' => false, 'message' => 'Pas de changement recuperé!']);
            exit;
        }
        $status = "";
        switch ($_POST['status']) {
            case 'active':
                $status = "active";
                break;
            case 'inactive':
                $status = "inactive";
                break;
            default:
                $status = "";
                break;
        }
        $result = $this->userModel->updateManagerStatus($_POST['id'], $status);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du status.']);
        }
    }
    public function updateManagerRole()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->logout();
            exit;
        }
        if (!isset($_POST['csrf_token']) || !$this->validateCsrftoken($_POST['csrf_token'])) {
            AppLog::error("CSRF token invalid pour l'essais de modification du rôle de compte : ");
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }
        // Vérification de l'ID utilisateur
        if (!isset($_POST['id']) || !is_numeric($_POST['id']) || $_SESSION['user_id'] == intval($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            return;
        }

        $user = $this->userModel->getUserById($_POST['id']);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            exit;
        }
        if ($user["status"] == $_POST["status"]) {
            echo json_encode(['success' => false, 'message' => 'Pas de changement récuperé!']);
            exit;
        }
        $status = "";
        switch ($_POST['status']) {
            case 'super_manager':
                $status = "super_manager";
                break;
            case 'manager':
                $status = "manager";
                break;
            default:
                $status = "";
                break;
        }
        $result = $this->userModel->updateManagerRole($_POST['id'], $status);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'rôle mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du rôle.']);
        }
    }
    public function updateUserProfile()
    {
        // Vérification de session
        if (!isset($_SESSION['user_id'])) {
            $this->logout();
            exit;
        }

        // Méthode invalide
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            return;
        }

        // Nettoyage des données
        $data = array_map(function ($value) {
            return htmlspecialchars(trim($value));
        }, $_POST);

        // Vérification du CSRF token
        if (empty($data['csrf_token']) || !$this->validateCsrftoken($data['csrf_token'])) {
            AppLog::error("CSRF token invalide lors de la mise à jour du profil.");
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Validation de l'ID
        if (!is_numeric($userId)) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            return;
        }

        // Récupération des données actuelles
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            return;
        }

        // Champs autorisés
        $allowedFields = ['username', 'email', 'phone', 'fullname'];
        $diffs = ['id' => $userId];

        foreach ($allowedFields as $field) {
            if (isset($data[$field]) && $data[$field] !== $user[$field]) {
                $diffs[$field] = $data[$field];
            }
        }

        if (count($diffs) === 1) { // seul 'id' est présent
            echo json_encode(['success' => false, 'message' => 'Aucune modification détectée.']);
            return;
        }

        // Validation uniquement des champs modifiés
        $errors = [];

        if (isset($diffs['username']) && !$this->validator->validateUsername($diffs['username'])) {
            $errors[] = "Nom d'utilisateur invalide (min. 3 caractères, alphanumérique ou underscore).";
        }

        if (isset($diffs['email']) && !$this->validator->validateEmail($diffs['email'])) {
            $errors[] = "Veuillez entrer une adresse e-mail valide.";
        }

        if (isset($diffs['phone']) && !$this->validator->validatePhone($diffs['phone'])) {
            $errors[] = "Numéro de téléphone invalide (10 à 15 chiffres requis).";
        }

        if (isset($diffs['fullname']) && (!preg_match('/^[a-zA-Z0-9\s]{5,}$/', $diffs['fullname']) || strlen($diffs['fullname']) > 50)) {
            $errors[] = "Nom complet invalide (min.5 max.50 caractères alphanumériques).";
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Mise à jour
        $updated = $this->userModel->updateUser($diffs);

        if ($updated) {
            AppLog::info("Profil de l'utilisateur ID $userId a été mis à jour avec succès.");
            echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
        } else {
            AppLog::warning("Échec de mise à jour : conflit possible sur email ou username pour utilisateur ID $userId.");
            echo json_encode(['success' => false, 'message' => 'Email ou nom d\'utilisateur déjà utilisé.']);
        }
    }
    public function updateManagerPassword()
    {
        if (
            !isset($_SESSION['user_id']) ||
            $_SESSION['user_role'] !== 'admin' ||
            !isset($_POST['csrf_token']) ||
            !$this->validateCsrftoken($_POST['csrf_token'])
        ) {
            $this->logout();
            exit;
        }
        if ($_SESSION['user_id'] == $_POST['id']) {
            echo json_encode(['success' => false, 'message' => "Action invalide! veuillez voir le log pour plus d'infos"]);
            AppLog::warning("Vous n'avez pas le droit de modifier le mot de passe administrateur directement");
            exit;
        }
        $data = $_POST;
        foreach ($data as $k => $v) {
            $data[$k] = htmlspecialchars(trim($v));
        }
        $user = $this->userModel->getUserById($data['id']);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            exit;
        }
        $strCtrl = '';
        $newData = [];
        if (!empty($data['password'])) {
            $newData['password'] = Security::hashPassword($data["password"]);
            $strCtrl .= 'p';
        }
        if (!empty($newData)) {
            if ($strCtrl !== '') {
                $newData['id'] = intval($data['id']);
                $updated = $this->userModel->updateUser($newData);
                if ($updated) {
                    echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du Mot de passe.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => true, 'message' => 'Pas de modification trouvée!.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune modification trouvée!.']);
            exit;
        }
    }
    public function getManagerProfle()
    {
        $result = $this->userModel->getUserById($_SESSION['user_id']);
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
            exit;
        } else {
            $this->sessionManager->logout();
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue!']);
        }
    }
    public function getInfos()
    {
        // Vérification de session
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->logout();
            exit;
        }

        // Vérification de la méthode de requête
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
            return;
        }

        // Vérification de l'ID utilisateur
        if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            return;
        }

        $id = intval(htmlspecialchars($_POST['id']));
        $result = $this->userModel->getUserById($id);

        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
        }
    }
    public function login(): bool
    {
        $data = [
            'csrf_token' => $_POST['csrf_token'] ?? '',
            'error' => '',
            'blocked' => false,
            'attempts' => 0
        ];

        // Vérification du token CSRF en premier
        if (!$this->validateCsrftoken($data['csrf_token'])) {
            AppLog::error("CSRF token invalide lors de la tentative de connexion.");
            Route::redirect("/login");
            return false;
        }

        // Récupération des données utilisateur
        $identifier = htmlentities(trim($_POST['identifier'] ?? ''));
        $password = $_POST['password'] ?? '';
        $ip_address = $_SESSION['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SESSION['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'];

        // Vérification des champs obligatoires
        if (empty($identifier) || empty($password)) {
            $data['error'] = $this->errorHandler->getSampleError('empty_fields');
            (new PageController())->login($data);
            return false;
        }

        // Vérifier si l'IP ou l'user agent sont bloqués
        if ($this->authenticator->is_blocked()) {
            $data['blocked'] = true;
            $data['error'] = $this->errorHandler->getSampleError('blocked_ip');
            (new PageController())->login($data);
            return false;
        }

        // Récupérer le nombre de tentatives de connexion échouées la derniere heure
        $attempts = $this->authenticator->loginAttempts($ip_address, $user_agent);

        // Bloquer après 5 tentatives échouées
        if ($attempts > 4) {
            $data['blocked'] = true;
            $data['error'] = $this->errorHandler->getSampleError('blocked_ip');
            $this->authenticator->setLoginStatus($ip_address, $user_agent, $attempts, 'blocked');
            (new PageController())->login($data);
            return false;
        }

        // Gestion des tentatives en session (protection immédiate)
        if (!isset($_SESSION['attempts'])) {
            $_SESSION['attempts'] = 1;
        }
        $_SESSION['attempts'] = 0;
        if ($_SESSION['attempts'] > 3) {
            $data['attempts'] = $_SESSION['attempts'];
            $data['blocked'] = true;
            $data['error'] = $this->errorHandler->getSampleError('login_attempt_over');
            (new PageController())->login($data);
            return false;
        }
        // Vérifier si l'utilisateur existe
        $user = $this->userModel->getUserByEmailUsernameOrPhone($identifier);
        if (!$user || !Security::verifyPassword(password: $password, hash: $user['password'])) {
            $_SESSION['attempts']++;
            $this->authenticator->recordLoginAttempt($ip_address, $user_agent, $_SESSION['attempts']);
            AppLog::warning("Tentative de connexion échouée pour : $identifier");
            $data['attempts'] = $_SESSION['attempts'];
            $data['error'] = $this->errorHandler->getSampleError('wrong_password');
            (new PageController())->login($data);
            return false;
        }

        // Vérifier si le compte est actif
        if ($user['status'] !== 'active') {
            (new PageController())->handleInactiveAccount();
            return false;
        }

        // Authentification réussie : Réinitialiser les tentatives
        unset($_SESSION['attempts']);
        $this->authenticator->recordLoginAttempt($ip_address, $user_agent, 0, 'success', $user['id']);
        $this->sessionManager->authenticate(
            user_id: $user['id'],
            user_role: $user['role'],
            full_name: $user['fullname'],
        );
        AppLog::info("Utilisateur connecté : {$user['id']}");
        header('Location: /dashboard');
        return true;
    }

    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn()
    {
        return $this->sessionManager->isAuthenticated();
    }

    // Récupérer les informations de l'utilisateur connecté
    public function getLoggedInUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $user_id = $_SESSION['user_id']; // Accéder à l'ID utilisateur dans la session
        return $this->userModel->getUserById($user_id); // Récupérer l'utilisateur
    }

    // Déconnecter un utilisateur
    public function logout()
    {
        $this->sessionManager->logout();
        Route::redirect('/home');
    }

    // Récupérer un utilisateur par son ID
    public function getUser($id)
    {
        if (!is_numeric($id)) {
            return $this->validator->errorhandler()->getSampleError('userid');
        }

        $user = $this->userModel->getUserById($id);

        if (!$user) {
            return $this->validator->errorhandler()->getSampleError('nouser');
        }

        return $user;
    }

    // Supprimer un utilisateur
    public function deleteUser()
    {
        $data = [];
        // Récupérer les données envoyées
        if (empty($_POST)) {
            echo json_encode(['success' => false, 'message' => 'Aucune donnée envoyée']);
            return;
        }
        $data = $_POST;
        foreach ($data as $k => $v) {
            $data[$k] = htmlspecialchars(trim($v));
        }

        // Vérifier si l'ID utilisateur est valide
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
            return;
        }

        $userId = htmlspecialchars($data['id']);
        if ($_SESSION['user_role'] === 'admin' && $_SESSION['user_id'] != $userId) {
            // Appeler la méthode de suppression du modèle User
            $result = $this->userModel->deleteUser($userId);

            if ($result) {
                echo json_encode(['success' => true]);
                return;
            } else {
                echo json_encode(['success' => false]);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour effectuer cette action.']);
            return;
        }
    }


    // Récupérer tous les utilisateurs
    public function getAllUsers()
    {
        $users = $this->userModel->getAllUsers();
        return !empty($users) ? json_encode($users) : "Aucun utilisateur trouvé.";
    }
    public function handleExpiration()
    {
        if ($this->sessionManager->checkSessionExpiration()) {
            echo json_encode(['expired' => true]);
            $this->sessionManager->destroy();
        } else {
            echo json_encode(['expired' => false]);
        }
    }

    // Retourne l'instance du validateur
    public function getValidator()
    {
        return $this->validator;
    }
    public function getsessionManager()
    {
        return $this->sessionManager;
    }

    // UserController.php
    public function generateCsrftoken()
    {
        $token = CSRFProtection::generateToken(); // Génère un token sécurisé
        $this->sessionManager->set('csrf_token', $token); // Stocke le token dans la session
        return $token;
    }

    public function validateCsrftoken($token)
    {
        return CSRFProtection::validateToken($token); // Comparaison sécurisée
    }
    public function passwordReset()
    {
        if (!isset($_SESSION['psdRL'])) {
            $_SESSION['psdRL'] = 0;
        } else {
            $_SESSION['psdRL']++;
        }
        if ($_SESSION['psdRL'] >= 2) {
            echo json_encode(['success' => false, 'message' => 'Tentatives de réinitialisation trop nombreuses.Veuillez réessayer plus tard.']);
            return;
        }
        $data = [];
        foreach ($_POST as $k => $v) {
            $data[$k] = htmlspecialchars(trim($v));
        }
        if (!$this->validateCsrftoken($data['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Token invalide.']);
            return;
        }
        if (empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer votre email.']);
            return;
        }
        if (!$this->validator->validateEmail($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            return;
        }
        $email = $data['email'];
        // Vérifier si l'utilisateur existe
        $user = $this->userModel->getUserByEmailUsernameOrPhone($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'L\'adresse email n\'existe pas.']);
            return;
        }
        if ($user['reset_pswd_limit'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue, Contactez votre administrateur!']);
            return;
        }
        if ($this->userModel->updateRL($email, $user['reset_pswd_limit'] - 1)) {
            // Générer un jeton de réinitialisation
            $token = bin2hex(random_bytes(32));
            $this->userModel->createPasswordResetToken($email, $token);

            // Envoyer un e-mail à l'utilisateur
            $resetLink = WEB_URL . "/reset-password?token=$token";
            // Envoi de l'e-mail (remplacez par votre propre fonction d'envoi d'e-mail)
            $status = $this->sendResetPassword($email, "Réinitialisation du mot de passe", "Cliquez sur le lien suivant pour réinitialiser votre mot de passe :", $resetLink);
            if ($status) {
                echo json_encode(['success' => true, 'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'e-mail.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue, Contactez votre administrateur!']);
            return;
        }
    }
    public function getUserByResetToken($token)
    {
        return $this->userModel->getUserByResetToken($token);
    }
    public function setNewPassword()
    {
        if (isset($_SESSION['attempts'])) {
            unset($_SESSION['attempts']);
        }
        $data = [];
        foreach ($_POST as $k => $v) {
            $data[$k] = htmlspecialchars(trim($v));
        }
        if (!$this->validateCsrftoken($data['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Token invalide.']);
            return;
        }
        if (empty($data['token'])) {
            echo json_encode(['success' => false, 'message' => 'Token invalide.']);
            return;
        }
        // Récupérer l'utilisateur via le jeton
        $user = $this->getUserByResetToken($data['token']);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Jeton invalide ou expiré.']);
            return;
        }

        if (empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Veuillez entrer votre nouveau mot de passe.']);
            return;
        }
        if (empty($data['confirm_password'])) {
            echo json_encode(['success' => false, 'message' => 'Veuillez confirmer votre nouveau mot de passe.']);
            return;
        }
        if ($data['password'] !== $data['confirm_password']) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            return;
        }
        if (strlen($data['password']) < 6) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.']);
            return;
        }

        // Hacher le nouveau mot de passe
        $hashedPassword = Security::hashPassword($data["password"]);
        $result = $this->userModel->setNewPassword($user['id'], $hashedPassword);
        if ($result) {
            // Supprimer le jeton de réinitialisation
            $this->userModel->clearResetToken($user['id']);

            echo json_encode(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la réinitialisation du mot de passe.']);
        }
    }
    private function checkAdmin()
    {
        return $this->userModel->checkAdmin();
    }
    public function setAdminAccount()
    {
        try {
            $id = random_int(1000, 9999);
            $user = $this->checkAdmin();
            if (!$user) {
                $password = Config::get("WEB_ADMIN_PASSWORD");

                $data = [
                    'id' => $id,
                    'username' => Config::get("WEB_ADMIN_USERNAME"),
                    'email' => Config::get("WEB_ADMIN_EMAIL"),
                    'phone' => Config::get("WEB_PHONE") ?? "+212666666666",
                    'last_password' => '',
                    'password' => Security::hashPassword($password),
                    'role' => 'admin',
                    'status' => 'active',
                    'reset_token' => "",
                ];
                $result = $this->userModel->setAdminAccount($data);
                if ($result) {
                    // Générer un jeton de réinitialisation
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->createPasswordResetToken(Config::get("WEB_ADMIN_EMAIL"), $token);
                    $resetLink = WEB_URL . "/reset-password?token=$token";
                    $status = $this->sendResetPassword(Config::get("WEB_ADMIN_EMAIL"), '🔐 Initialisation de votre mot de passe administrateur', 'Votre compte administrateur a été créé avec succès. Pour des raisons de sécurité, veuillez initialiser votre mot de passe en suivant le lien ci-dessous :', $resetLink);
                    if (!$status) {
                        AppLog::error("Erreur lors de la création du compte administrateur.");
                        return false;
                    }
                    AppLog::info("Compte administrateur créé avec succès.");
                    return true;
                } else {
                    AppLog::error("Erreur lors de la création du compte administrateur.");
                    return false;
                }
            } else {
                AppLog::error("Compte administrateur déja créé!");
                return true;
            }
        } catch (\Exception $th) {
            return false;
        }
    }
    private function generateStrongPassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        $allChars = $uppercase . $lowercase . $digits . $specialChars;

        // Assurer qu'on a au moins un caractère de chaque type
        $password = $uppercase[random_int(0, strlen($uppercase) - 1)] .
            $lowercase[random_int(0, strlen($lowercase) - 1)] .
            $digits[random_int(0, strlen($digits) - 1)] .
            $specialChars[random_int(0, strlen($specialChars) - 1)];

        // Compléter le reste du mot de passe
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mélanger le mot de passe pour plus d'aléatoire
        return str_shuffle($password);
    }

    public function sendFeedback()
    {
        $data = $_POST;
        $name = $data['name'];
        $email = $data['email'];
        $message = $data['message'];
        $subject = "📩 Réponse à votre message";

        // Style CSS inline pour l'email
        $css = '
        body { font-family: "Segoe UI", Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4A6FDC; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; }
        .header h2 { margin: 0; font-weight: 500; }
        .content { background-color: #fff; padding: 20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; margin-bottom: 15px; color: #2C3E50; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .message-content { background-color: #f9f9f9; padding: 15px; border-radius: 6px; font-style: italic; }
        .footer { margin-top: 20px; font-size: 14px; color: #555; text-align: center; }
        ';

        // Construire le corps de l'email en HTML
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Réponse à votre message</title>
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>💬 Réponse à votre message</h2>
                </div>
                <div class="content">
                    <div class="section">
                        <div class="section-title">👤 Cher ' . htmlspecialchars($name) . ',</div>
                        <p>Merci pour votre message ! Nous avons bien reçu votre demande et voici notre réponse :</p>
                        <div class="message-content">' . nl2br(htmlspecialchars($message)) . '</div>
                        <p>Si vous avez d\'autres questions, n\'hésitez pas à nous contacter.</p>
                    </div>
                    <div class="footer">
                        📩 Cet email est envoyé automatiquement. Merci de ne pas y répondre directement.
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Envoi de l'email
        $status = $this->mailer->quickSend($email, $subject, $htmlBody, true);
        if ($status) {
            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message.']);
        }
        exit;
    }
    public function sendResetPassword($email, $subject, $message, $link)
    {

        // Style CSS inline pour l'email
        $css = '
    body { font-family: "Segoe UI", Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; }
    .container { max-width: 600px; margin: 20px auto; padding: 20px; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .header { background-color: #4A6FDC; color: white; padding: 15px; border-radius: 8px 8px 0 0; text-align: center; }
    .header h2 { margin: 0; font-size: 20px; font-weight: 500; }
    .content { padding: 20px; }
    .section-title { font-size: 18px; font-weight: 600; color: #2C3E50; margin-bottom: 15px; border-bottom: 2px solid #4A6FDC; padding-bottom: 5px; }
    .message-content { background-color: #f9f9f9; padding: 15px; border-radius: 6px; font-style: italic; text-align: center; }
    .button { display: inline-block; margin-top: 15px; padding: 10px 15px; background:rgb(255, 206, 114); color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .footer { margin-top: 20px; font-size: 14px; color: #555; text-align: center; }
    ';

        // Construire le corps de l'email en HTML
        $htmlBody = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Initialisation de votre mot de passe</title>
        <style>' . $css . '</style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>🔐 Initialisation du mot de passe</h2>
            </div>
            <div class="content">
                <div class="section-title">👋 Bonjour,</div>
                <p>' . $message . '</p>
                <div class="message-content">
                    <a href="' . $link . '" class="button">🔑 Initialiser mon mot de passe</a>
                </div>
                <p><strong>⚠️ Attention :</strong> Ce lien expirera dans une heure. Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer cet email.</p>
            </div>
            <div class="footer">
                📩 Cet email est envoyé automatiquement. Merci de ne pas y répondre directement.
            </div>
        </div>
    </body>
    </html>';

        // Envoi de l'email
        return $this->mailer->quickSend($email, $subject, $htmlBody, true);
    }
}
