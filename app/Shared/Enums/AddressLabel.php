<?php

namespace App\Shared\Enums;

enum AddressLabel: string
{
    case HOME = 'Home';
    case OFFICE = 'Office';
    case COTTAGE = 'Cottage';
    case FAMILY = 'Family';
    case OTHER = 'Other';

    /**
     * Get all available labels
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label options for dropdowns
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->value;
        }
        return $options;
    }

    /**
     * Check if label is valid
     */
    public static function isValid(string $label): bool
    {
        return in_array($label, self::values());
    }
}