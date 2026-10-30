<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | DNS checks on email addresses (email:rfc,dns) are on in production and
    | switched off in tests so the suite never performs network lookups.
    |
    */

    'verify_email_dns' => (bool) env('ENQUIRY_VERIFY_EMAIL_DNS', true),

    /*
    |--------------------------------------------------------------------------
    | Abuse protection
    |--------------------------------------------------------------------------
    */

    // Submissions allowed per IP per form within the decay window.
    'rate_limit' => [
        'max_attempts' => (int) env('ENQUIRY_RATE_LIMIT', 5),
        'decay_seconds' => (int) env('ENQUIRY_RATE_LIMIT_DECAY', 600),
    ],

    // Forms submitted faster than this after rendering are treated as bots.
    'minimum_fill_seconds' => (int) env('ENQUIRY_MIN_FILL_SECONDS', 3),

    // "turnstile" or "none". Turnstile also needs TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY.
    'captcha' => env('ENQUIRY_CAPTCHA', 'none'),

    /*
    |--------------------------------------------------------------------------
    | Quote drafts ("Save and continue later")
    |--------------------------------------------------------------------------
    */

    'draft_lifetime_days' => (int) env('QUOTE_DRAFT_LIFETIME_DAYS', 14),

];
