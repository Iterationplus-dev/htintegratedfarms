<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\QueryException;

/**
 * Cached access to editable site settings (phones, emails, address, WhatsApp…).
 * Values saved in the admin panel override the defaults in config/site.php.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site-settings.v1';

    /** @var array<string, string>|null */
    private ?array $resolved = null;

    public function __construct(private Cache $cache, private Config $config) {}

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        /** @var array<string, string> $defaults */
        $defaults = array_map('strval', (array) $this->config->get('site.defaults', []));

        return $this->resolved = array_merge($defaults, $this->stored());
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->all()[$key] ?? '';

        return $value !== '' ? $value : $default;
    }

    public function flush(): void
    {
        $this->resolved = null;
        $this->cache->forget(self::CACHE_KEY);
    }

    /** "tel:" href from a display number, e.g. "+234 810 780 7012" → "tel:+2348107807012". */
    public function telHref(string $phone): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $phone);
    }

    public function whatsappUrl(?string $message = null): string
    {
        $number = preg_replace('/\D/', '', $this->get('whatsapp_number'));
        $url = "https://wa.me/{$number}";

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }

    /**
     * Sales/admin inboxes that receive new-enquiry notifications.
     *
     * @return list<string>
     */
    public function enquiryRecipients(): array
    {
        $emails = array_map('trim', explode(',', $this->get('enquiry_recipients')));

        return array_values(array_filter($emails, fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }

    /**
     * @return list<string>
     */
    public function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $this->get($key)) ?: [])));
    }

    /**
     * @return array<string, string>
     */
    private function stored(): array
    {
        try {
            /** @var array<string, string> */
            return $this->cache->rememberForever(self::CACHE_KEY, fn (): array => Setting::query()
                ->whereNotNull('value')
                ->pluck('value', 'key')
                ->map(fn (mixed $value): string => (string) $value)
                ->all());
        } catch (QueryException) {
            // Database not migrated yet (fresh install, build step): fall back to defaults.
            return [];
        }
    }
}
