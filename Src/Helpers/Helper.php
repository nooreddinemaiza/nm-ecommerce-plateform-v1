<?php

namespace Src\Helpers;

use DateTime;

class Helper
{
    public static function combineDataByKey($data, $key)
    {
        $combinedData = [];
        foreach ($data as $item) {
            $uniqueKey = $item[$key];
            if (!isset($combinedData[$uniqueKey])) {
                $combinedData[$uniqueKey] = $item;
            } else {
                foreach ($item as $field => $value) {
                    if ($field !== $key) {
                        // Vérification si une donnée est différente
                        if ($combinedData[$uniqueKey][$field] !== $value) {
                            // Si c'est déjà un tableau, ajouter la valeur si elle est unique
                            if (is_array($combinedData[$uniqueKey][$field])) {
                                if (!in_array($value, $combinedData[$uniqueKey][$field])) {
                                    $combinedData[$uniqueKey][$field][] = $value;
                                }
                            } else {
                                // Convertir en tableau si une différence est trouvée
                                $combinedData[$uniqueKey][$field] = [$combinedData[$uniqueKey][$field], $value];
                            }
                        }
                    }
                }
            }
        }

        return array_values($combinedData);
    }

    public static function safeJsonDecode($data, $key)
    {
        // Vérifie si la clé existe et que les données ne sont pas vides
        if (isset($data[$key]) && !empty($data[$key])) {
            // Si la valeur est une chaîne JSON, on tente de la décoder
            if (is_string($data[$key])) {
                $decodedData = json_decode($data[$key], true); // Essayer de décoder la chaîne en tableau
                // Si le décodage réussit (on obtient un tableau ou un objet), on remplace la valeur
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$key] = $decodedData;
                }
            }

            // Vérifier si c'est un tableau (ou objet) et transformer les objets en tableaux
            if (is_array($data[$key])) {
                foreach ($data[$key] as $k => $v) {
                    // Si l'élément est un objet, on le transforme en tableau associatif
                    if (is_object($v)) {
                        $data[$key][$k] = json_decode(json_encode($v), true);
                    } else if (is_string($v)) {
                        // Appliquer htmlspecialchars pour échapper les caractères spéciaux
                        $data[$key][$k] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
                    }
                }
            }
        } else {
            // Si les données sont vides ou inexistantes, on initialise la clé à un tableau vide
            $data[$key] = [];
        }
        return $data;
    }

    public static function formatTimeDifference($createdAt)
    {
        $dateCreated = new DateTime($createdAt);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $dateCreated->getTimestamp();

        if ($diff < 60) return "$diff secondes";
        $diffInMinutes = floor($diff / 60);
        if ($diffInMinutes < 60) return "$diffInMinutes minutes";
        $diffInHours = floor($diffInMinutes / 60);
        if ($diffInHours < 24) return "$diffInHours heures";
        $diffInDays = floor($diffInHours / 24);
        return "$diffInDays jours";
    }
    
    public static function TimeDifference($createdAt)
    {
        $dateCreated = new DateTime($createdAt);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $dateCreated->getTimestamp();
        $time = [
            'secondes' => 0,
            'minutes' => 0, 
            'heures' => 0,
            'jours' => 0
        ];
        if ($diff < 60) {
            $time['secondes'] = $diff;
        }
        $diffInMinutes = floor($diff / 60);
        if ($diffInMinutes < 60) {
            $time['minutes'] = $diffInMinutes;
        }
        $diffInHours = floor($diffInMinutes / 60);
        if ($diffInHours < 24) {
            $time['heures'] = $diffInHours;
        }
        $diffInDays = floor($diffInHours / 24);
        $time['jours'] = $diffInDays;
        return $time;
    }
}
