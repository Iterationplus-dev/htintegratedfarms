<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts Nigerian numbers in the handoff format (^(\+234|0)[789][01]\d{8}$) or any
 * E.164 international number (UK partners use +44). Spaces, dots, dashes and brackets are ignored.
 */
class PhoneNumber implements ValidationRule
{
    private const NIGERIAN = '/^(\+234|0)[789][01]\d{8}$/';

    private const INTERNATIONAL = '/^\+[1-9]\d{7,14}$/';

    public static function normalize(string $value): string
    {
        $value = preg_replace('/[\s().\-]/', '', trim($value)) ?? '';

        return str_starts_with($value, '00') ? '+'.substr($value, 2) : $value;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Enter a valid phone number.');

            return;
        }

        $normalized = self::normalize($value);

        if (str_starts_with($normalized, '+234') || str_starts_with($normalized, '0')) {
            if (preg_match(self::NIGERIAN, $normalized) !== 1) {
                $fail('Enter a valid Nigerian number, e.g. 0810 780 7012 or +234 810 780 7012.');
            }

            return;
        }

        if (preg_match(self::INTERNATIONAL, $normalized) !== 1) {
            $fail('Enter a valid phone number, including the country code for numbers outside Nigeria.');
        }
    }
}
