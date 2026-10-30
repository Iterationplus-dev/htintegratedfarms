<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AreaOfInterest: string implements HasLabel
{
    case LivestockPurchase = 'livestock_purchase';
    case Poultry = 'poultry';
    case Eggs = 'eggs';
    case PalmProducts = 'palm_products';
    case CommercialSupply = 'commercial_supply';
    case Partnership = 'partnership';
    case Investment = 'investment';
    case FarmVisit = 'farm_visit';
    case GeneralInquiry = 'general_inquiry';

    public function getLabel(): string
    {
        return match ($this) {
            self::LivestockPurchase => 'Livestock purchase',
            self::Poultry => 'Poultry',
            self::Eggs => 'Eggs',
            self::PalmProducts => 'Palm products',
            self::CommercialSupply => 'Commercial supply',
            self::Partnership => 'Partnership',
            self::Investment => 'Investment',
            self::FarmVisit => 'Farm visit',
            self::GeneralInquiry => 'General inquiry',
        };
    }
}
