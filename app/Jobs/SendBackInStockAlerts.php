<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\StockAlert;
use App\Notifications\ProductBackInStock;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies pending "Notify me" subscribers for a product. Each alert is marked as notified
 * as it is sent, so a retried job never emails the same subscriber twice.
 */
class SendBackInStockAlerts implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Product $product) {}

    public function uniqueId(): string
    {
        return (string) $this->product->getKey();
    }

    public function handle(): void
    {
        if (! $this->product->isQuotable() || ! $this->product->is_published) {
            return;
        }

        StockAlert::query()
            ->where('product_id', $this->product->getKey())
            ->pending()
            ->chunkById(100, function (Collection $alerts): void {
                foreach ($alerts as $alert) {
                    /** @var StockAlert $alert */
                    $claimed = StockAlert::query()
                        ->whereKey($alert->getKey())
                        ->whereNull('notified_at')
                        ->update(['notified_at' => now()]);

                    if ($claimed === 1) {
                        Notification::route('mail', $alert->email)->notify(new ProductBackInStock($this->product));
                    }
                }
            });
    }
}
