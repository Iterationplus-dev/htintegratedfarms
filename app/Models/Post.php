<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $title
 * @property string $body
 * @property string|null $excerpt
 * @property string $category_label
 * @property string|null $topic
 * @property int $reading_minutes
 * @property Carbon|null $published_at
 */
#[Fillable(['author_id', 'title', 'slug', 'excerpt', 'body', 'category_label', 'topic', 'reading_minutes', 'published_at'])]
class Post extends Model implements HasMedia
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasResponsiveImages;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reading_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function coverImage(): ?Media
    {
        return $this->getFirstMedia('cover');
    }

    /** Body HTML with anything outside a safe allowlist removed (Symfony HtmlSanitizer via Filament). */
    public function safeBody(): string
    {
        return Str::sanitizeHtml($this->body);
    }

    /** Eyebrow line, e.g. "Poultry · 4 min read". */
    public function eyebrow(): string
    {
        return "{$this->category_label} · {$this->reading_minutes} min read";
    }
}
