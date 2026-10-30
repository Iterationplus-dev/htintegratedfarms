<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GalleryCategory: string implements HasLabel
{
    case Farm = 'farm';
    case Cattle = 'cattle';
    case Poultry = 'poultry';
    case PalmPlantation = 'palm-plantation';
    case Processing = 'processing';
    case Equipment = 'equipment';
    case FarmTeam = 'farm-team';

    public function getLabel(): string
    {
        return match ($this) {
            self::Farm => 'Farm',
            self::Cattle => 'Cattle',
            self::Poultry => 'Poultry',
            self::PalmPlantation => 'Palm Plantation',
            self::Processing => 'Processing',
            self::Equipment => 'Equipment',
            self::FarmTeam => 'Farm Team',
        };
    }

    /**
     * Categories offered as filter chips, in design order. "Farm" (landscape shots) only appears under "All".
     *
     * @return list<self>
     */
    public static function filterable(): array
    {
        return [self::Cattle, self::Poultry, self::PalmPlantation, self::Processing, self::Equipment, self::FarmTeam];
    }
}
