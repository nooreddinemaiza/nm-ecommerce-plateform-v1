<?php

namespace Src\Helpers;

use Src\Helpers\ErrorHandler;

class Validator
{
    private $errors = [];
    private $errorhandler;

    public function __construct()
    {
        $this->errorhandler = new ErrorHandler;
    }

    /**
     * Valider une chaîne non vide avec une longueur minimale.
     */
    public function validateString($value, $minLength = 3)
    {
        if (empty($value) || strlen($value) < $minLength) {
            $this->errors['string'] = $this->errorhandler->getSampleError('str') . $this->errorhandler->getSampleError('strlen');
        }
    }
    public function validateUsername($value, $maxLength = 15)
    {
        // Vérification de la longueur minimale et maximale
        if (strlen($value) < 4 || strlen($value) > $maxLength) {
            $this->errors['username'] = "Le nom d'utilisateur doit comporter entre 8 et $maxLength caractères.";
            return false;
        }

        // Vérification des règles de validation du username
        $regex = '/^[a-z0-9_]{3,}$/';

        if (!preg_match($regex, $value)) {
            $this->errors['username'] = "Nom d'utilisateur invalide : il doit commencer par 3 lettres et ne contenir que des lettres, chiffres ou underscores (_).";
            return false;
        }

        // Vérification que les underscores ne sont pas consécutifs ni en début/fin
        if (strpos($value, '__') !== false || $value[0] === '_' || substr($value, -1) === '_') {
            $this->errors['username'] = "Le nom d'utilisateur ne doit pas commencer ou finir par un underscore (_) ni en contenir plusieurs consécutifs.";
            return false;
        }
        return true;
    }

    /**
     * Valider une adresse email.
     */
    public function validateEmail($value)
    {
        if (empty($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = $this->errorhandler->getSampleError('email');
            return false;
        }
        return true;
    }

    /**
     * Valider un numéro de téléphone.
     */
    public function validatePhone($value)
    {
        if (empty($value) || !preg_match('/^\d{10,15}$/', $value)) {
            $this->errors['phone'] = $this->errorhandler->getSampleError('phone');
            return false;
        }
        return true;
    }

    /**
     * Valider un mot de passe.
     */
    public function validatePassword($value)
    {
        $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';

        if (empty($value)) {
            $this->errors['password'] = $this->errorhandler->getSampleError('empty');
        } elseif (!preg_match($regex, $value)) {
            $this->errors['password'] = $this->errorhandler->getSampleError('weak_password');
        }
    }

    /**
     * Valider un nombre.
     */
    public function validateNumber($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            $this->errors['number'] = "Valeur non numérique.";
            return;
        }

        if ($min !== null && $value < $min) {
            $this->errors['number'] = "La valeur doit être au moins $min.";
        }
        if ($max !== null && $value > $max) {
            $this->errors['number'] = "La valeur doit être inférieure ou égale à $max.";
        }
    }

    /**
     * Valider une date.
     */
    public function validateDate($value, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            $this->errors['date'] = "Date invalide, format attendu : $format.";
        }
    }

    /**
     * Valider une URL.
     */
    public function validateURL($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors['url'] = "URL invalide.";
        }
    }

    /**
     * Vérifier si les données sont valides.
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * Récupérer les erreurs.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Récupérer l'ErrorHandler.
     */
    public function errorhandler()
    {
        return $this->errorhandler;
    }
}
