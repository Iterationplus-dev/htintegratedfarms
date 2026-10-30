<?php

namespace App\Models;

use Database\Factories\QuoteDraftFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * "Save and continue later" state for the quote wizard. Resumed through a temporary signed URL.
 *
 * @property array<string, mixed> $data
 * @property string $uuid
 * @property string $email
 * @property Carbon $expires_at
 * @property Carbon|null $resumed_at
 */
#[Fillable(['uuid', 'email', 'data', 'expires_at', 'resumed_at'])]
class QuoteDraft extends Model
{
    /** @use HasFactory<QuoteDraftFactory> */
    use HasFactory, MassPrunable;

    public const LIFETIME_DAYS = 14;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'expires_at' => 'datetime',
            'resumed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Expired drafts are removed by the scheduled `model:prune` command.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return self::query()->where('expires_at', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
