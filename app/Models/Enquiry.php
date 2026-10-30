<?php

namespace App\Models;

use App\Enums\AreaOfInterest;
use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Enums\PartnershipType;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property EnquiryType $type
 * @property EnquiryStatus $status
 * @property string $name
 * @property string|null $organisation
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $subject
 * @property string|null $message
 * @property array<string, mixed>|null $payload
 * @property int|null $assigned_to
 * @property Carbon|null $responded_at
 */
#[Fillable([
    'reference', 'type', 'status', 'name', 'organisation', 'email', 'phone', 'subject', 'message',
    'payload', 'assigned_to', 'idempotency_key', 'ip_hash', 'user_agent', 'responded_at',
])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    /** Unambiguous characters for references (no 0/O, 1/I/L). */
    private const REFERENCE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EnquiryType::class,
            'status' => EnquiryStatus::class,
            'payload' => 'array',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Enquiry $enquiry): void {
            $enquiry->status ??= EnquiryStatus::New;
            if (! $enquiry->reference) {
                $enquiry->reference = self::generateReference($enquiry->type);
            }
        });
    }

    public static function generateReference(EnquiryType $type): string
    {
        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= self::REFERENCE_ALPHABET[random_int(0, strlen(self::REFERENCE_ALPHABET) - 1)];
            }
            $reference = sprintf('%s-%s-%s', $type->referencePrefix(), now()->format('ym'), $suffix);
        } while (self::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @return HasMany<QuoteItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [EnquiryStatus::New, EnquiryStatus::InProgress]);
    }

    /** Human-readable subject: area of interest (contact) or partnership type. */
    public function subjectLabel(): ?string
    {
        if ($this->subject === null) {
            return null;
        }

        $label = match ($this->type) {
            EnquiryType::Contact => AreaOfInterest::tryFrom($this->subject)?->getLabel(),
            EnquiryType::Partnership => PartnershipType::tryFrom($this->subject)?->getLabel(),
            EnquiryType::Quote => null,
        };

        return $label ?? $this->subject;
    }
}
