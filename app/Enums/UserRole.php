<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'admin';
    case Sales = 'sales';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Sales => 'Sales',
            self::Editor => 'Content editor',
        };
    }

    public function canManageEnquiries(): bool
    {
        return in_array($this, [self::Admin, self::Sales], true);
    }

    public function canManageContent(): bool
    {
        return in_array($this, [self::Admin, self::Editor], true);
    }
}
