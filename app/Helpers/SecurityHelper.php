<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitize($value)
    {
        return strip_tags($value);
    }

    public static function sanitizeWithTags($value, $allowedTags = '<b><i><u><p><br><strong><em>')
    {
        return strip_tags($value, $allowedTags);
    }

    public static function escapeJson($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function sanitizeFilename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }

    public static function escapeLike($value)
    {
        return addslashes(str_replace(['%', '_'], ['\%', '\_'], $value));
    }
}
