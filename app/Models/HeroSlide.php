<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Database\Factories\HeroSlideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'eyebrow', 'mobile_eyebrow', 'title', 'body', 'mobile_body', 'image_alt', 'image_position',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'is_active', 'sort',
])]
class HeroSlide extends Model implements HasMedia
{
    /** @use HasFactory<HeroSlideFactory> */
    use HasFactory, HasResponsiveImages;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function photo(): ?Media
    {
        return $this->getFirstMedia('image');
    }
}
