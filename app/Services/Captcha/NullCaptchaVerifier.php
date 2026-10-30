<?php

namespace App\Services\Captcha;

use App\Contracts\CaptchaVerifier;

/** Used when ENQUIRY_CAPTCHA=none: honeypot, timing and rate limiting still apply. */
class NullCaptchaVerifier implements CaptchaVerifier
{
    public function enabled(): bool
    {
        return false;
    }

    public function siteKey(): ?string
    {
        return null;
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        return true;
    }
}
