<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EnquiryStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Responded = 'responded';
    case Closed = 'closed';
    case Spam = 'spam';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::Responded => 'Responded',
            self::Closed => 'Closed',
            self::Spam => 'Spam',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::InProgress => 'info',
            self::Responded => 'success',
            self::Closed => 'gray',
            self::Spam => 'danger',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::InProgress], true);
    }
}
