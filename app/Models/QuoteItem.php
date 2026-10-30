<?php

namespace App\Models;

use App\Enums\QuoteFrequency;
use Database\Factories\QuoteItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $product_name
 * @property int $quantity
 * @property string|null $unit
 * @property QuoteFrequency $frequency
 */
#[Fillable(['enquiry_id', 'product_id', 'product_name', 'quantity', 'unit', 'frequency'])]
class QuoteItem extends Model
{
    /** @use HasFactory<QuoteItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'frequency' => QuoteFrequency::class,
        ];
    }

    /**
     * @return BelongsTo<Enquiry, $this>
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** e.g. "400 crates / week". */
    public function summary(): string
    {
        return trim(number_format($this->quantity).' '.($this->unit ?? '')).' '.$this->frequency->summarySuffix();
    }
}
