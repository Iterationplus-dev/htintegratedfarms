/**
 * Consent-gated analytics loader. The server prints the provider config as JSON in
 * <script id="ht-analytics-config" type="application/json">; nothing third-party is loaded
 * until the visitor grants analytics consent.
 */

export type AnalyticsProvider = 'none' | 'plausible' | 'ga4';

export interface AnalyticsConfig {
    provider: AnalyticsProvider;
    id: string;
    src?: string;
}

declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: (...args: unknown[]) => void;
    }
}

let loaded = false;

export function readAnalyticsConfig(): AnalyticsConfig | null {
    const el = document.getElementById('ht-analytics-config');
    if (!el?.textContent) {
        return null;
    }
    try {
        const parsed = JSON.parse(el.textContent) as Partial<AnalyticsConfig>;
        if ((parsed.provider === 'plausible' || parsed.provider === 'ga4') && typeof parsed.id === 'string' && parsed.id !== '') {
            const config: AnalyticsConfig = { provider: parsed.provider, id: parsed.id };
            if (typeof parsed.src === 'string' && parsed.src !== '') {
                config.src = parsed.src;
            }

            return config;
        }
    } catch {
        // Malformed config: behave as if analytics is disabled.
    }

    return null;
}

function injectScript(src: string, attributes: Record<string, string> = {}): void {
    const script = document.createElement('script');
    script.async = true;
    script.src = src;
    for (const [name, value] of Object.entries(attributes)) {
        script.setAttribute(name, value);
    }
    document.head.appendChild(script);
}

export function loadAnalytics(config: AnalyticsConfig | null): void {
    if (loaded || !config) {
        return;
    }
    loaded = true;

    if (config.provider === 'plausible') {
        injectScript(config.src ?? 'https://plausible.io/js/script.js', { 'data-domain': config.id, defer: '' });

        return;
    }

    window.dataLayer = window.dataLayer ?? [];
    window.gtag = function gtag(...args: unknown[]): void {
        window.dataLayer?.push(args);
    };
    window.gtag('js', new Date());
    window.gtag('config', config.id, { anonymize_ip: true });
    injectScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(config.id)}`);
}

/** Best-effort removal of analytics cookies after consent is withdrawn. */
export function clearAnalyticsCookies(): void {
    const host = window.location.hostname;
    for (const part of document.cookie.split('; ')) {
        const name = part.split('=')[0] ?? '';
        if (name.startsWith('_ga') || name.startsWith('_gid')) {
            for (const domain of ['', `; Domain=${host}`, `; Domain=.${host.replace(/^www\./, '')}`]) {
                document.cookie = `${name}=; Max-Age=0; Path=/${domain}`;
            }
        }
    }
}
