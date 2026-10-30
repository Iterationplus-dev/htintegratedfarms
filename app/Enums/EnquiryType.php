<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EnquiryType: string implements HasColor, HasLabel
{
    case Contact = 'contact';
    case Quote = 'quote';
    case Partnership = 'partnership';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contact => 'Contact message',
            self::Quote => 'Quote request',
            self::Partnership => 'Partnership inquiry',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contact => 'gray',
            self::Quote => 'success',
            self::Partnership => 'info',
        };
    }

    /** Prefix used in human-readable enquiry references, e.g. HTQ-2610-7K3F9. */
    public function referencePrefix(): string
    {
        return match ($this) {
            self::Contact => 'HTC',
            self::Quote => 'HTQ',
            self::Partnership => 'HTP',
        };
    }
}
