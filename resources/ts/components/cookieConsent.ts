import { readCookie, writeCookie } from '../lib/cookies';
import { clearAnalyticsCookies, loadAnalytics, readAnalyticsConfig } from '../lib/analytics';

export const CONSENT_COOKIE = 'ht_consent';
export const CONSENT_VERSION = 1;
const CONSENT_DAYS = 180;

export interface ConsentRecord {
    essential: true;
    analytics: boolean;
    v: number;
    ts: number;
}

export interface ConsentStore {
    decided: boolean;
    analytics: boolean;
    draftAnalytics: boolean;
    preferencesOpen: boolean;
    init(): void;
    acceptAll(): void;
    rejectNonEssential(): void;
    openPreferences(): void;
    closePreferences(): void;
    savePreferences(): void;
    persist(analytics: boolean): void;
}

export function parseConsent(raw: string | null): ConsentRecord | null {
    if (!raw) {
        return null;
    }
    try {
        const value = JSON.parse(raw) as Partial<ConsentRecord>;
        if (value.v !== CONSENT_VERSION || typeof value.analytics !== 'boolean') {
            return null;
        }

        return { essential: true, analytics: value.analytics, v: CONSENT_VERSION, ts: Number(value.ts) || 0 };
    } catch {
        return null;
    }
}

/**
 * Global consent store (Alpine.store('consent')): shared by the banner, the preferences dialog
 * and the footer "Cookie settings" link. Choice is kept for 180 days in a first-party cookie.
 */
export function createConsentStore(): ConsentStore {
    return {
        decided: true,
        analytics: false,
        draftAnalytics: false,
        preferencesOpen: false,

        init(): void {
            const record = parseConsent(readCookie(CONSENT_COOKIE));
            this.decided = record !== null;
            this.analytics = record?.analytics ?? false;
            if (this.analytics) {
                loadAnalytics(readAnalyticsConfig());
            }
        },

        acceptAll(): void {
            this.persist(true);
        },

        rejectNonEssential(): void {
            this.persist(false);
        },

        openPreferences(): void {
            this.draftAnalytics = this.analytics;
            this.preferencesOpen = true;
        },

        closePreferences(): void {
            this.preferencesOpen = false;
        },

        savePreferences(): void {
            this.persist(this.draftAnalytics);
            this.preferencesOpen = false;
        },

        persist(analytics: boolean): void {
            const record: ConsentRecord = { essential: true, analytics, v: CONSENT_VERSION, ts: Date.now() };
            writeCookie(CONSENT_COOKIE, JSON.stringify(record), CONSENT_DAYS);
            const withdrawn = this.analytics && !analytics;
            this.analytics = analytics;
            this.decided = true;
            if (analytics) {
                loadAnalytics(readAnalyticsConfig());
            } else if (withdrawn) {
                clearAnalyticsCookies();
            }
        },
    };
}
