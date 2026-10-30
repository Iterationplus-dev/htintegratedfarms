<?php

namespace App\Actions\Quotes;

use App\Models\QuoteDraft;
use App\Notifications\QuoteDraftSaved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/** "Save and continue later": stores the wizard state and emails a temporary signed resume link. */
class SaveQuoteDraft
{
    /**
     * @param  array<string, mixed>  $state
     */
    public function handle(string $email, array $state): QuoteDraft
    {
        return DB::transaction(function () use ($email, $state): QuoteDraft {
            $draft = QuoteDraft::create([
                'uuid' => (string) Str::uuid(),
                'email' => $email,
                'data' => $state,
                'expires_at' => now()->addDays((int) config('enquiries.draft_lifetime_days', QuoteDraft::LIFETIME_DAYS)),
            ]);

            Notification::route('mail', $email)->notify(new QuoteDraftSaved($draft));

            return $draft;
        });
    }
}
