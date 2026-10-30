<?php

namespace App\Models;

use App\Enums\GalleryCategory;
use App\Enums\GallerySpan;
use App\Models\Concerns\HasResponsiveImages;
use Database\Factories\GalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $title
 * @property GalleryCategory $category
 * @property GallerySpan|null $span
 */
#[Fillable(['title', 'category', 'span', 'is_featured', 'is_published', 'sort'])]
class GalleryImage extends Model implements HasMedia
{
    /** @use HasFactory<GalleryImageFactory> */
    use HasFactory, HasResponsiveImages;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => GalleryCategory::class,
            'span' => GallerySpan::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function photo(): ?Media
    {
        return $this->getFirstMedia('image');
    }
}
