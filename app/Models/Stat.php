<?php

namespace App\Models;

use Database\Factories\StatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/** Editable headline figures (homepage strip, About band, Palm and Sustainability bands). */
#[Fillable(['key', 'page', 'value', 'label', 'short_label', 'sort'])]
class Stat extends Model
{
    /** @use HasFactory<StatFactory> */
    use HasFactory;

    public const PAGES = [
        'home' => 'Homepage',
        'about' => 'About Us',
        'palm' => 'Palm Production',
        'sustainability' => 'Sustainability',
    ];

    protected static function booted(): void
    {
        static::saved(fn (Stat $stat) => Cache::forget("stats.{$stat->page}"));
        static::deleted(fn (Stat $stat) => Cache::forget("stats.{$stat->page}"));
    }

    /**
     * @return Collection<int, Stat>
     */
    public static function forPage(string $page): Collection
    {
        // Cache plain attribute arrays: Laravel's cache refuses to unserialize objects by default.
        $rows = Cache::rememberForever("stats.{$page}", fn (): array => static::query()
            ->where('page', $page)
            ->orderBy('sort')
            ->get()
            ->map(fn (Stat $stat): array => $stat->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public function compactLabel(): string
    {
        return $this->short_label ?: $this->label;
    }
}
