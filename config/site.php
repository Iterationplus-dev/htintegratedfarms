<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default site settings
    |--------------------------------------------------------------------------
    |
    | Fallback values for App\Services\SiteSettings. Values saved in the admin
    | panel (settings table) take precedence. Contact details are taken from
    | the approved design handoff; the street address is still a placeholder
    | until the client confirms it.
    |
    */

    'defaults' => [
        'company_name' => 'H & T Integrated Farms Limited',
        'domain_label' => 'htintegratedfarms.com',
        'tagline' => 'Growing a better tomorrow. Integrated livestock, poultry and oil palm production in Ogun State, Nigeria.',
        'phone_primary' => '+234 810 780 7012',
        'phone_secondary' => '+234 805 046 0470',
        'phone_uk' => '+44 7760 892302',
        'email_info' => 'info@htintegratedfarms.com',
        'email_partners' => 'partners@htintegratedfarms.com',
        'whatsapp_number' => '+234 810 780 7012',
        'address' => "Farm address, LGA\nOgun State, Nigeria",
        'working_hours' => "Monday–Friday 08:00–17:00\nSaturday 09:00–14:00",
        'map_embed_url' => '',
        'map_directions_url' => 'https://www.google.com/maps/search/?api=1&query=Ogun+State%2C+Nigeria',
        'enquiry_recipients' => env('ENQUIRY_NOTIFICATION_EMAIL', 'info@htintegratedfarms.com'),
        'facebook_url' => '',
        'instagram_url' => '',
        'linkedin_url' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics (loaded only after the visitor grants analytics consent)
    |--------------------------------------------------------------------------
    */

    'analytics' => [
        'provider' => env('ANALYTICS_PROVIDER', 'none'), // none | plausible | ga4
        'id' => env('ANALYTICS_ID', ''),               // Plausible domain or GA4 measurement ID
        'src' => env('ANALYTICS_SCRIPT_SRC', ''),      // optional self-hosted Plausible script URL
    ],

];
