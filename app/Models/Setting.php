<?php

namespace App\Models;

use App\Services\SiteSettings;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Key/value site settings; read through App\Services\SiteSettings (cached). */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => app(SiteSettings::class)->flush());
        static::deleted(fn () => app(SiteSettings::class)->flush());
    }
}
