import type { AlpineComponent } from 'alpinejs';

export interface GalleryItem {
    id: number;
    title: string;
    category: string;
    categoryLabel: string;
    src: string;
    srcset: string;
}

interface Gallery {
    items: GalleryItem[];
    category: string;
    openIndex: number | null;
    touchStartX: number | null;
    readonly visible: GalleryItem[];
    readonly current: GalleryItem | null;
    readonly meta: string;
    matches(category: string): boolean;
    setCategory(category: string): void;
    open(id: number): void;
    close(): void;
    next(): void;
    prev(): void;
    onKeydown(event: KeyboardEvent): void;
    onTouchStart(event: TouchEvent): void;
    onTouchEnd(event: TouchEvent): void;
}

const SWIPE_THRESHOLD_PX = 40;

/**
 * Gallery chip filter + lightbox (handoff §08). Tiles are server-rendered, so the page works
 * without JS; this component only hides tiles and drives the dialog. The lightbox steps through
 * the currently filtered set.
 */
export function gallery(items: GalleryItem[], initialCategory = 'all'): AlpineComponent<Gallery> {
    return {
        items,
        category: initialCategory,
        openIndex: null,
        touchStartX: null,

        get visible(): GalleryItem[] {
            return this.category === 'all' ? this.items : this.items.filter((item) => item.category === this.category);
        },

        get current(): GalleryItem | null {
            return this.openIndex === null ? null : (this.visible[this.openIndex] ?? null);
        },

        get meta(): string {
            if (this.openIndex === null || !this.current) {
                return '';
            }

            return `${this.current.categoryLabel} · ${this.openIndex + 1} of ${this.visible.length}`;
        },

        matches(category: string): boolean {
            return this.category === 'all' || this.category === category;
        },

        setCategory(category: string): void {
            this.category = category;
            const url = new URL(window.location.href);
            if (category === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', category);
            }
            window.history.replaceState(window.history.state, '', url);
        },

        open(id: number): void {
            const index = this.visible.findIndex((item) => item.id === id);
            this.openIndex = index === -1 ? null : index;
        },

        close(): void {
            this.openIndex = null;
        },

        next(): void {
            if (this.openIndex !== null && this.visible.length > 0) {
                this.openIndex = (this.openIndex + 1) % this.visible.length;
            }
        },

        prev(): void {
            if (this.openIndex !== null && this.visible.length > 0) {
                this.openIndex = (this.openIndex - 1 + this.visible.length) % this.visible.length;
            }
        },

        onKeydown(event: KeyboardEvent): void {
            if (this.openIndex === null) {
                return;
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.next();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.prev();
            } else if (event.key === 'Escape') {
                this.close();
            }
        },

        onTouchStart(event: TouchEvent): void {
            this.touchStartX = event.changedTouches[0]?.clientX ?? null;
        },

        onTouchEnd(event: TouchEvent): void {
            const endX = event.changedTouches[0]?.clientX;
            if (this.touchStartX === null || endX === undefined) {
                return;
            }
            const delta = endX - this.touchStartX;
            this.touchStartX = null;
            if (Math.abs(delta) >= SWIPE_THRESHOLD_PX) {
                if (delta < 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        },
    };
}
