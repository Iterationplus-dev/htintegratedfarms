<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GallerySpan: string implements HasLabel
{
    case Wide = 'wide';
    case Tall = 'tall';
    case Large = 'large';

    public function getLabel(): string
    {
        return match ($this) {
            self::Wide => 'Wide (2 × 1)',
            self::Tall => 'Tall (1 × 2)',
            self::Large => 'Large (2 × 2)',
        };
    }

    /** Grid placement in the 2-col mobile / 4-col desktop dense grid. */
    public function gridClasses(): string
    {
        return match ($this) {
            self::Wide => 'col-span-2',
            self::Tall => 'row-span-2',
            self::Large => 'col-span-2 md:row-span-2',
        };
    }
}
