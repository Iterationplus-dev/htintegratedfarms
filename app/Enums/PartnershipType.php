<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PartnershipType: string implements HasLabel
{
    case Distributor = 'distributor';
    case Retailer = 'retailer';
    case AgriculturalSupplier = 'agricultural_supplier';
    case VeterinaryServices = 'veterinary_services';
    case FeedSupplier = 'feed_supplier';
    case Processor = 'processor';
    case FinancialPartner = 'financial_partner';
    case Investor = 'investor';
    case GovernmentProgramme = 'government_programme';
    case Institution = 'institution';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Distributor => 'Distributors',
            self::Retailer => 'Retailers',
            self::AgriculturalSupplier => 'Agricultural suppliers',
            self::VeterinaryServices => 'Veterinary services',
            self::FeedSupplier => 'Feed suppliers',
            self::Processor => 'Processors',
            self::FinancialPartner => 'Financial partners',
            self::Investor => 'Investors',
            self::GovernmentProgramme => 'Government programmes',
            self::Institution => 'Institutions',
            self::Other => 'Other',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Distributor => 'Regional volume supply agreements.',
            self::Retailer => 'Supermarkets, shops and market traders.',
            self::AgriculturalSupplier => 'Inputs, machinery and consumables.',
            self::VeterinaryServices => 'Herd and flock health programmes.',
            self::FeedSupplier => 'Raw materials and finished rations.',
            self::Processor => 'Off-take of FFB, poultry and oil.',
            self::FinancialPartner => 'Working capital and expansion finance.',
            self::Investor => 'Equity participation in new capacity.',
            self::GovernmentProgramme => 'Agricultural schemes and extension.',
            self::Institution => 'Research, training and development bodies.',
            self::Other => 'Tell us about it in your proposal.',
        };
    }

    /** Short label for the compact mobile grid. */
    public function shortLabel(): string
    {
        return match ($this) {
            self::VeterinaryServices => 'Veterinary',
            default => $this->getLabel(),
        };
    }

    /**
     * Partner types shown as cards (everything except "Other").
     *
     * @return list<self>
     */
    public static function showcased(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type !== self::Other));
    }
}
