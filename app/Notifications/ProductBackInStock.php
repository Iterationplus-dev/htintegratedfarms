<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent once to each "Notify me" subscriber when a product becomes available again. */
class ProductBackInStock extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public Product $product)
    {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->product->name} is available again")
            ->greeting("{$this->product->name} is available again")
            ->line("You asked us to let you know when {$this->product->name} could be ordered. It is now listed as {$this->product->availability->getLabel()}.")
            ->action('Request a quote', route('quote', ['product' => $this->product->slug]))
            ->line('You will not receive further alerts for this product unless you sign up again.');
    }
}
