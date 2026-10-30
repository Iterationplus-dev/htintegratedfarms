<?php

namespace App\Models;

use App\Enums\ProductAvailability;
use App\Models\Concerns\HasResponsiveImages;
use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $summary
 * @property string|null $featured_summary
 * @property string|null $description
 * @property list<array{label: string, value: string}>|null $specs
 * @property ProductAvailability $availability
 * @property string|null $unit
 * @property bool $is_featured
 * @property bool $is_published
 */
#[Fillable([
    'product_category_id', 'name', 'slug', 'summary', 'featured_summary', 'description', 'specs',
    'availability', 'unit', 'is_featured', 'is_published', 'sort',
])]
#[ObservedBy(ProductObserver::class)]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasResponsiveImages;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'availability' => ProductAvailability::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<StockAlert, $this>
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
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
        $query->orderBy('sort')->orderBy('name');
    }

    public function cover(): ?Media
    {
        return $this->getFirstMedia('images');
    }

    public function isQuotable(): bool
    {
        return $this->availability->isQuotable();
    }

    /** Category line shown above the title, e.g. "Farm Produce". */
    public function categoryName(): string
    {
        return $this->category->name ?? '';
    }
}
