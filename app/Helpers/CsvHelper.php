<?php

namespace App\Helpers;

class CsvHelper
{
    /**
     * Prevent CSV Formula Injection.
     */
    public static function sanitize($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        // Remove leading whitespace before checking
        $trimmed = ltrim($value);

        // Prevent Excel/Google Sheets formula execution
        if (preg_match('/^[=\-+@]/', $trimmed)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitize an entire CSV row.
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitize'], $row);
    }
    
}