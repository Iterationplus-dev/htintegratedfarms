import type { AlpineComponent } from 'alpinejs';
import { prefersReducedMotion } from '../lib/cookies';

interface HeroSlider {
    count: number;
    intervalMs: number;
    active: number;
    hovering: boolean;
    focused: boolean;
    reducedMotion: boolean;
    timer: number | null;
    onVisibility: (() => void) | null;
    readonly counter: string;
    readonly announcement: string;
    isActive(index: number): boolean;
    go(index: number): void;
    next(): void;
    prev(): void;
    start(): void;
    stop(): void;
    setHover(state: boolean): void;
    setFocus(state: boolean): void;
}

/**
 * Homepage hero carousel (handoff §Interactions): cross-fade + Ken Burns, autoplay every 6s,
 * manual navigation resets the timer, pauses on hover/focus/hidden tab and when reduced motion is preferred.
 */
export function heroSlider(count: number, intervalMs = 6000): AlpineComponent<HeroSlider> {
    return {
        count,
        intervalMs,
        active: 0,
        hovering: false,
        focused: false,
        reducedMotion: false,
        timer: null,
        onVisibility: null,

        get counter(): string {
            return String(this.active + 1).padStart(2, '0');
        },

        get announcement(): string {
            return `Slide ${this.active + 1} of ${this.count}`;
        },

        init(): void {
            this.reducedMotion = prefersReducedMotion();
            this.onVisibility = () => (document.hidden ? this.stop() : this.start());
            document.addEventListener('visibilitychange', this.onVisibility);
            this.start();
        },

        destroy(): void {
            this.stop();
            if (this.onVisibility) {
                document.removeEventListener('visibilitychange', this.onVisibility);
            }
        },

        isActive(index: number): boolean {
            return this.active === index;
        },

        go(index: number): void {
            this.active = (index + this.count) % this.count;
            this.start();
        },

        next(): void {
            this.go(this.active + 1);
        },

        prev(): void {
            this.go(this.active - 1);
        },

        start(): void {
            this.stop();
            if (this.reducedMotion || this.hovering || this.focused || this.count < 2 || document.hidden) {
                return;
            }
            this.timer = window.setInterval(() => {
                this.active = (this.active + 1) % this.count;
            }, this.intervalMs);
        },

        stop(): void {
            if (this.timer !== null) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },

        setHover(state: boolean): void {
            this.hovering = state;
            this.start();
        },

        setFocus(state: boolean): void {
            this.focused = state;
            this.start();
        },
    };
}
