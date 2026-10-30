<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductAvailability: string implements HasColor, HasLabel
{
    case InStock = 'in_stock';
    case Seasonal = 'seasonal';
    case OnRequest = 'on_request';
    case OutOfSeason = 'out_of_season';

    public function getLabel(): string
    {
        return match ($this) {
            self::InStock => 'In stock',
            self::Seasonal => 'Seasonal',
            self::OnRequest => 'On request',
            self::OutOfSeason => 'Out of season',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::Seasonal => 'info',
            self::OnRequest => 'warning',
            self::OutOfSeason => 'gray',
        };
    }

    /** Pill badge classes from the design (image overlay badge). */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::InStock => 'bg-g700 text-white',
            self::Seasonal => 'bg-navy-600 text-white',
            self::OnRequest => 'bg-earth text-white',
            self::OutOfSeason => 'bg-muted-chip text-ink-2',
        };
    }

    /** Inline status text colour used on compact (mobile) cards. */
    public function textClass(): string
    {
        return match ($this) {
            self::InStock => 'text-g600',
            self::Seasonal => 'text-navy-600',
            self::OnRequest => 'text-earth',
            self::OutOfSeason => 'text-ink-3',
        };
    }

    /** Out-of-season products offer "Notify me" instead of a quote. */
    public function isQuotable(): bool
    {
        return $this !== self::OutOfSeason;
    }
}
