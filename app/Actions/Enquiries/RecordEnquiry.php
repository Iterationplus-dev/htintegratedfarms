<?php

namespace App\Actions\Enquiries;

use App\Enums\EnquiryType;
use App\Enums\QuoteFrequency;
use App\Models\Enquiry;
use App\Notifications\EnquiryReceivedConfirmation;
use App\Notifications\NewEnquiryReceived;
use App\Services\SiteSettings;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Persists a contact, quote or partnership enquiry and queues the sales alert and auto-reply.
 *
 * Idempotent: the caller supplies a per-form `idempotency_key`. A repeated submission (double click,
 * network retry, back-button resubmit) returns the original enquiry and does not notify again.
 * The enquiry and its quote items are written in one transaction; notifications are queued
 * after commit, so a failed write never sends email.
 */
class RecordEnquiry
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * @param  array{
     *     type: EnquiryType,
     *     idempotency_key: string,
     *     name: string,
     *     organisation?: string|null,
     *     email?: string|null,
     *     phone?: string|null,
     *     subject?: string|null,
     *     message?: string|null,
     *     payload?: array<string, mixed>|null,
     *     ip?: string|null,
     *     user_agent?: string|null,
     * }  $data
     * @param  list<array{product_id: int|null, product_name: string, quantity: int, unit: string|null, frequency: QuoteFrequency}>  $items
     */
    public function handle(array $data, array $items = []): Enquiry
    {
        $existing = Enquiry::query()->where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data, $items): Enquiry {
                $enquiry = Enquiry::create([
                    'type' => $data['type'],
                    'idempotency_key' => $data['idempotency_key'],
                    'name' => $data['name'],
                    'organisation' => self::clean($data['organisation'] ?? null),
                    'email' => self::clean($data['email'] ?? null),
                    'phone' => self::clean($data['phone'] ?? null),
                    'subject' => $data['subject'] ?? null,
                    'message' => self::clean($data['message'] ?? null),
                    'payload' => $data['payload'] ?? null,
                    'ip_hash' => isset($data['ip']) ? hash_hmac('sha256', $data['ip'], (string) config('app.key')) : null,
                    'user_agent' => isset($data['user_agent']) ? mb_substr($data['user_agent'], 0, 255) : null,
                ]);

                foreach ($items as $item) {
                    $enquiry->items()->create($item);
                }

                $this->notify($enquiry);

                return $enquiry;
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent request with the same key won the race; return its record.
            return Enquiry::query()->where('idempotency_key', $data['idempotency_key'])->firstOrFail();
        }
    }

    private function notify(Enquiry $enquiry): void
    {
        $recipients = $this->settings->enquiryRecipients();

        if ($recipients === []) {
            Log::warning('No enquiry notification recipients configured.', ['reference' => $enquiry->reference]);
        } else {
            Notification::route('mail', $recipients)->notify(new NewEnquiryReceived($enquiry));
        }

        if ($enquiry->email) {
            Notification::route('mail', [$enquiry->email => $enquiry->name])->notify(new EnquiryReceivedConfirmation($enquiry));
        }
    }

    private static function clean(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
