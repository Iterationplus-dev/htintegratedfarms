<?php

namespace App\Notifications;

use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Models\QuoteItem;
use App\Services\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Auto-reply to the person who submitted an enquiry. */
class EnquiryReceivedConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    public function __construct(public Enquiry $enquiry)
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
        $settings = app(SiteSettings::class);
        $enquiry = $this->enquiry->loadMissing('items');

        $intro = match ($enquiry->type) {
            EnquiryType::Quote => 'Thank you for your quote request. Our sales team will reply by email with pricing, availability and a delivery window, typically within one working day.',
            EnquiryType::Partnership => 'Thank you for your partnership inquiry. It has been passed to the management team, who review every proposal and answer directly.',
            EnquiryType::Contact => 'Thank you for getting in touch. A member of our team will reply shortly.',
        };

        $mail = (new MailMessage)
            ->subject("We have received your enquiry ({$enquiry->reference})")
            ->greeting("Hello {$enquiry->name},")
            ->line($intro)
            ->line("Your reference is **{$enquiry->reference}**. Please quote it if you contact us about this request.");

        foreach ($enquiry->items as $item) {
            /** @var QuoteItem $item */
            $mail->line("- {$item->product_name}: {$item->summary()}");
        }

        return $mail
            ->line('If your request is urgent, call us on '.$settings->get('phone_primary').'.')
            ->salutation('H & T Integrated Farms Limited');
    }
}
