<?php

namespace App\Notifications;

use App\Models\QuoteDraft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/** Emails the signed "continue your quote request" link. */
class QuoteDraftSaved extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public QuoteDraft $draft)
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

    public function resumeUrl(): string
    {
        return URL::temporarySignedRoute('quote.resume', $this->draft->expires_at, ['draft' => $this->draft->uuid]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Continue your quote request — H & T Integrated Farms')
            ->greeting('Your quote request is saved')
            ->line('Use the button below to pick up where you left off. Nothing has been sent to our sales team yet.')
            ->action('Continue quote request', $this->resumeUrl())
            ->line('This link expires on '.$this->draft->expires_at->format('j F Y').'. If you did not request it, you can ignore this email.');
    }
}
