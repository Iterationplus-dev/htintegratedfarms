<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\QuoteItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

/** Internal alert to the sales inbox(es) for every new contact, quote or partnership enquiry. */
class NewEnquiryReceived extends Notification implements ShouldQueue
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
        $enquiry = $this->enquiry->loadMissing('items');

        $mail = (new MailMessage)
            ->subject("[{$enquiry->reference}] New {$enquiry->type->getLabel()} from {$enquiry->name}")
            ->greeting("New {$enquiry->type->getLabel()}")
            ->line("**Reference:** {$enquiry->reference}")
            ->line("**Name:** {$enquiry->name}");

        if ($enquiry->organisation) {
            $mail->line("**Organisation:** {$enquiry->organisation}");
        }
        if ($enquiry->email) {
            $mail->line("**Email:** {$enquiry->email}");
        }
        if ($enquiry->phone) {
            $mail->line("**Phone:** {$enquiry->phone}");
        }
        if ($subject = $enquiry->subjectLabel()) {
            $mail->line("**Subject:** {$subject}");
        }

        foreach ($enquiry->items as $item) {
            /** @var QuoteItem $item */
            $mail->line("- {$item->product_name}: {$item->summary()}");
        }

        $payload = $enquiry->payload ?? [];
        if (! empty($payload['delivery_location'])) {
            $mail->line('**Delivery location:** '.$payload['delivery_location']);
        }
        if (! empty($payload['required_by'])) {
            $mail->line('**Required by:** '.$payload['required_by']);
        }
        if ($enquiry->message) {
            $mail->line('**Message:**')->line($enquiry->message);
        }

        if (Route::has('filament.admin.resources.enquiries.view')) {
            $mail->action('Open in admin', route('filament.admin.resources.enquiries.view', $enquiry));
        }

        if ($enquiry->email) {
            $mail->replyTo($enquiry->email, $enquiry->name);
        }

        return $mail;
    }
}
