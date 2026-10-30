/** Minimal first-party cookie helpers (no dependencies). */

export function readCookie(name: string): string | null {
    const prefix = `${encodeURIComponent(name)}=`;
    for (const part of document.cookie.split('; ')) {
        if (part.startsWith(prefix)) {
            return decodeURIComponent(part.slice(prefix.length));
        }
    }

    return null;
}

export function writeCookie(name: string, value: string, maxAgeDays: number): void {
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    const maxAge = Math.round(maxAgeDays * 24 * 60 * 60);
    document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; Max-Age=${maxAge}; Path=/; SameSite=Lax${secure}`;
}

export function prefersReducedMotion(): boolean {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
