<?php

namespace App\Livewire\Concerns;

use App\Contracts\CaptchaVerifier;
use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

/**
 * Abuse protection shared by public Livewire forms: honeypot field, minimum fill time,
 * per-IP rate limiting and optional Turnstile verification. Also provides the per-render
 * idempotency key that RecordEnquiry uses to discard duplicate submissions.
 */
trait ProtectsAgainstSpam
{
    /** Honeypot: hidden from people, filled by naive bots. */
    public string $website = '';

    public string $captchaToken = '';

    #[Locked]
    public int $renderedAt = 0;

    #[Locked]
    public string $submissionKey = '';

    public function mountProtectsAgainstSpam(): void
    {
        $this->renderedAt = now()->getTimestamp();
        $this->submissionKey = (string) Str::uuid();
    }

    abstract protected function rateLimitKey(): string;

    /**
     * Returns false (and adds a visible error where appropriate) when the submission must not be processed.
     * Honeypot hits are dropped silently so bots get no signal.
     */
    protected function passesSpamChecks(): bool
    {
        if ($this->website !== '') {
            return false;
        }

        if (now()->getTimestamp() - $this->renderedAt < (int) config('enquiries.minimum_fill_seconds')) {
            $this->addError('form', 'That was quicker than we expected. Please check your details and submit again.');

            return false;
        }

        $key = $this->rateLimitKey().'|'.request()->ip();
        $maxAttempts = (int) config('enquiries.rate_limit.max_attempts');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError('form', "Too many submissions from your connection. Please try again in {$minutes} minute(s), or call us directly.");

            return false;
        }

        $captcha = app(CaptchaVerifier::class);
        if ($captcha->enabled() && ! $captcha->verify($this->captchaToken, request()->ip())) {
            $this->captchaToken = '';
            $this->dispatch('captcha-reset');
            $this->addError('captchaToken', 'Please complete the verification check and try again.');

            return false;
        }

        RateLimiter::hit($key, (int) config('enquiries.rate_limit.decay_seconds'));

        return true;
    }

    /**
     * @return list<string>
     */
    protected function emailRules(): array
    {
        return ['required', 'string', 'max:190', config('enquiries.verify_email_dns') ? 'email:rfc,dns' : 'email:rfc'];
    }

    /**
     * @return list<mixed>
     */
    protected function phoneRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:30', new PhoneNumber];
    }

    /**
     * Fields that must never be sent to the browser after a successful submit.
     */
    protected function resetSpamState(): void
    {
        $this->captchaToken = '';
        $this->submissionKey = (string) Str::uuid();
        $this->renderedAt = now()->getTimestamp();
    }
}
