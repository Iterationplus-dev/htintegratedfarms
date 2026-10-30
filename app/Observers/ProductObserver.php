<?php

namespace App\Observers;

use App\Enums\ProductAvailability;
use App\Jobs\SendBackInStockAlerts;
use App\Models\Product;

class ProductObserver
{
    /**
     * When a product leaves "Out of season", alert its "Notify me" subscribers.
     */
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('availability')) {
            return;
        }

        $previous = $product->getOriginal('availability');
        $previous = $previous instanceof ProductAvailability ? $previous : ProductAvailability::tryFrom((string) $previous);

        if ($previous === ProductAvailability::OutOfSeason && $product->isQuotable()) {
            SendBackInStockAlerts::dispatch($product)->afterCommit();
        }
    }
}
