<?php

namespace App\Contracts;

interface CaptchaVerifier
{
    /** Whether a challenge must be rendered and verified. */
    public function enabled(): bool;

    /** Public site key for the browser widget (null when disabled). */
    public function siteKey(): ?string;

    public function verify(?string $token, ?string $ip = null): bool;
}
