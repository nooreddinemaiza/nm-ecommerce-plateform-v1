<?php

namespace Src\Helpers;

use Src\Helpers\AppLog;

class EmailSpam
{
    private static array $spamDomains = [
        'temp-mail.org',
        'tempmail.com',
        'throwawaymail.com',
        'mailinator.com',
        'guerrillamail.com',
        'yopmail.com',
        'mail.com',
        'getairmail.com',
        'fakeinbox.com',
        'sharklasers.com',
        'trashmail.com',
        '10minutemail.com',
        'dispostable.com',
        'jetable.org'
    ];

    public static function checkSpamEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            AppLog::warning("Invalid email: $email");
            return true;
        }

        $domain = strtolower(substr(strrchr($email, "@"), 1));
        if (in_array($domain, self::$spamDomains, true)) {
            AppLog::warning("Spam domain detected: $domain");
            return true;
        }

        $name = strstr($email, '@', true);
        $suspiciousPatterns = [
            '/\d{4,}@/',
            '/[^a-z0-9.-]+/i',
            '/[0-9]{8,}/',
            '/(.)\1{4,}/',
            '/(test|spam|fake|temp)/',
            '/^[0-9]+[a-z]+@/',
            '/[a-z]{15,}/'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, strtolower($name))) {
                AppLog::warning("Suspicious pattern detected: $pattern");
                return true;
            }
        }

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
        ];

        $tldParts = explode('.', $domain);
        $tld = strtolower(end($tldParts));
        if (!in_array($tld, $validTlds, true)) {
            return true;
        }

        $entropy = self::calculateEntropy($domain);
        if ($entropy > 3.5) {
            AppLog::warning("High entropy detected: $entropy");
            return true;
        }

        if (!checkdnsrr($domain, 'MX')) {
            return true;
        }

        return false;
    }

    private static function calculateEntropy(string $string): float
    {
        $length = strlen($string);
        if ($length === 0) {
            return 0.0;
        }
        $frequencies = array_count_values(str_split($string));
        $entropy = 0.0;
        foreach ($frequencies as $freq) {
            $probability = $freq / $length;
            $entropy -= $probability * log($probability, 2);
        }
        return $entropy;
    }
}
