<?php

namespace App\Services\Captcha;

use App\Contracts\CaptchaVerifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile server-side verification.
 *
 * A token that Cloudflare explicitly rejects fails verification. If Cloudflare itself is unreachable
 * or returns a 5xx, we fail open and log a warning, so an outage there does not block every
 * enquiry; the honeypot, timing check and rate limiter still apply.
 */
class TurnstileVerifier implements CaptchaVerifier
{
    public function __construct(
        private Http $http,
        private string $siteKey,
        private string $secretKey,
        private string $verifyUrl,
        private int $timeoutSeconds = 5,
    ) {}

    public function enabled(): bool
    {
        return true;
    }

    public function siteKey(): ?string
    {
        return $this->siteKey;
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        try {
            $response = $this->http->asForm()
                ->timeout($this->timeoutSeconds)
                ->post($this->verifyUrl, array_filter([
                    'secret' => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (ConnectionException $exception) {
            Log::warning('Turnstile unreachable; accepting submission.', ['error' => $exception->getMessage()]);

            return true;
        }

        if ($response->serverError()) {
            Log::warning('Turnstile returned a server error; accepting submission.', ['status' => $response->status()]);

            return true;
        }

        return $response->successful() && $response->json('success') === true;
    }
}
