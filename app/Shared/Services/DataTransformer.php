<?php

namespace App\Shared\Services;

use Carbon\Carbon;

/**
 * Data Transformer Service
 * 
 * Global data transformation utility for standardizing DB query results
 * Provides consistent field formatting across all services and modules
 */
class DataTransformer
{
    // =================== Date/Time Formatters ===================

    /**
     * Transform database timestamps to consistent format
     *
     * @param mixed $value The timestamp value
     * @param string $format The desired format
     * @return string|null
     */
    public static function formatDate($value, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform database timestamps to ISO format
     *
     * @param mixed $value The timestamp value
     * @return string|null
     */
    public static function formatDateIso($value): ?string
    {
        return self::formatDate($value, 'c'); // ISO 8601 format
    }

    /**
     * Transform database timestamps to human readable format
     *
     * @param mixed $value The timestamp value
     * @return string|null
     */
    public static function formatDateHuman($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->diffForHumans();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format date for display (e.g., "Nov 11, 2025")
     *
     * @param mixed $value The timestamp value
     * @return string|null
     */
    public static function formatDateDisplay($value): ?string
    {
        return self::formatDate($value, 'M j, Y');
    }

    /**
     * Format datetime for display (e.g., "Nov 11, 2025 at 2:30 PM")
     *
     * @param mixed $value The timestamp value
     * @return string|null
     */
    public static function formatDatetimeDisplay($value): ?string
    {
        return self::formatDate($value, 'M j, Y \a\t g:i A');
    }

    // =================== Type Formatters ===================

    /**
     * Format boolean values consistently
     *
     * @param mixed $value The boolean value
     * @return bool
     */
    public static function formatBoolean($value): bool
    {
        return (bool) $value;
    }

    /**
     * Format integer values
     *
     * @param mixed $value The integer value
     * @return int|null
     */
    public static function formatInteger($value): ?int
    {
        return $value !== null ? (int) $value : null;
    }

    /**
     * Format float values
     *
     * @param mixed $value The float value
     * @param int $decimals Number of decimal places
     * @return float|null
     */
    public static function formatFloat($value, int $decimals = 2): ?float
    {
        return $value !== null ? (float) number_format($value, $decimals, '.', '') : null;
    }

    /**
     * Format string values (trim and null handling)
     *
     * @param mixed $value The string value
     * @return string|null
     */
    public static function formatString($value): ?string
    {
        return $value !== null ? trim((string) $value) : null;
    }

    /**
     * Format money/currency values
     *
     * @param mixed $value The money value
     * @param int $decimals Number of decimal places
     * @param string $currency Currency symbol
     * @return string|null
     */
    public static function formatMoney($value, int $decimals = 2, string $currency = '$'): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return $currency . number_format((float) $value, $decimals);
    }

    /**
     * Format percentage values
     *
     * @param mixed $value The percentage value (0-100 or 0-1)
     * @param int $decimals Number of decimal places
     * @param bool $isDecimal Whether value is in decimal format (0-1)
     * @return string|null
     */
    public static function formatPercentage($value, int $decimals = 1, bool $isDecimal = false): ?string
    {
        if ($value === null) {
            return null;
        }

        $percentage = $isDecimal ? $value * 100 : $value;
        return number_format($percentage, $decimals) . '%';
    }

    // =================== JSON/Array Formatters ===================

    /**
     * Transform JSON fields to arrays
     *
     * @param mixed $value The JSON value
     * @return array|null
     */
    public static function formatJson($value): ?array
    {
        if (!$value) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        try {
            return json_decode($value, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform array to JSON string
     *
     * @param mixed $value The array value
     * @return string|null
     */
    public static function formatToJson($value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_encode($value, JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return null;
        }
    }

    // =================== Special Formatters ===================

    /**
     * Format phone numbers to consistent format
     *
     * @param mixed $value The phone number
     * @param string|null $format Format pattern (e.g., '(###) ###-####')
     * @return string|null
     */
    public static function formatPhoneNumber($value, ?string $format = null): ?string
    {
        if (!$value) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($phone) === 10 && $format === '(###) ###-####') {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        }

        return $phone;
    }

    /**
     * Format full name from first and last name
     *
     * @param string|null $firstName
     * @param string|null $lastName
     * @param bool $lastFirst Format as "Last, First"
     * @return string|null
     */
    public static function formatFullName(?string $firstName, ?string $lastName, bool $lastFirst = false): ?string
    {
        $first = self::formatString($firstName);
        $last = self::formatString($lastName);

        if (!$first && !$last) {
            return null;
        }

        if ($lastFirst) {
            return trim(($last ?? '') . ($first ? ', ' . $first : ''));
        }

        return trim(($first ?? '') . ($last ? ' ' . $last : ''));
    }

    /**
     * Format file size in human readable format
     *
     * @param int|null $bytes File size in bytes
     * @param int $precision Decimal precision
     * @return string|null
     */
    public static function formatFileSize(?int $bytes, int $precision = 1): ?string
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clean and sanitize data array (remove null values, empty strings, etc.)
     *
     * @param array $data Data array to clean
     * @param bool $removeEmpty Remove empty strings
     * @param bool $removeNull Remove null values
     * @return array Cleaned data array
     */
    public static function cleanData(array $data, bool $removeEmpty = false, bool $removeNull = false): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if ($removeNull && $value === null) {
                continue;
            }

            if ($removeEmpty && $value === '') {
                continue;
            }

            $cleaned[$key] = $value;
        }

        return $cleaned;
    }

    /**
     * Convert stdClass objects to arrays recursively
     *
     * @param mixed $data Data to convert
     * @return mixed Converted data
     */
    public static function stdClassToArray($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (is_array($data)) {
            return array_map([self::class, 'stdClassToArray'], $data);
        }

        return $data;
    }
}