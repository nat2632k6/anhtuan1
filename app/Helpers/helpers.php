<?php

use App\Helpers\SecurityHelper;

if (!function_exists('escape')) {
    function escape($value)
    {
        return SecurityHelper::escape($value);
    }
}

if (!function_exists('sanitize')) {
    function sanitize($value)
    {
        return SecurityHelper::sanitize($value);
    }
}

if (!function_exists('sanitizeWithTags')) {
    function sanitizeWithTags($value, $allowedTags = '<b><i><u><p><br><strong><em>')
    {
        return SecurityHelper::sanitizeWithTags($value, $allowedTags);
    }
}

if (!function_exists('escapeJson')) {
    function escapeJson($value)
    {
        return SecurityHelper::escapeJson($value);
    }
}
