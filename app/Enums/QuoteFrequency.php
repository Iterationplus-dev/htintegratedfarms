<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuoteFrequency: string implements HasLabel
{
    case OneOff = 'one_off';
    case Weekly = 'weekly';
    case Fortnightly = 'fortnightly';
    case Monthly = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::OneOff => 'One-off',
            self::Weekly => 'Weekly',
            self::Fortnightly => 'Fortnightly',
            self::Monthly => 'Monthly',
        };
    }

    /** Suffix for summaries, e.g. "400 crates / week". */
    public function summarySuffix(): string
    {
        return match ($this) {
            self::OneOff => '(one-off)',
            self::Weekly => '/ week',
            self::Fortnightly => '/ fortnight',
            self::Monthly => '/ month',
        };
    }
}
