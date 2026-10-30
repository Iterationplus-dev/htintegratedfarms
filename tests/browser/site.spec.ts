import { expect, test, type Page } from '@playwright/test';

const isMobile = (page: Page): boolean => (page.viewportSize()?.width ?? 1440) < 768;

async function acceptCookies(page: Page): Promise<void> {
    const banner = page.getByRole('region', { name: 'We value your privacy' });
    if (await banner.isVisible()) {
        await banner.getByRole('button', { name: 'Accept all' }).click();
        await expect(banner).toBeHidden();
    }
}

test.describe('cookie consent', () => {
    test('banner records the choice in a first-party cookie and stays dismissed', async ({ page, context }) => {
        await page.goto('/');
        const banner = page.getByRole('region', { name: 'We value your privacy' });
        await expect(banner).toBeVisible();

        await banner.getByRole('button', { name: isMobile(page) ? 'Reject all' : 'Reject non-essential' }).click();
        await expect(banner).toBeHidden();

        const consent = (await context.cookies()).find((c) => c.name === 'ht_consent');
        expect(consent).toBeDefined();
        expect(JSON.parse(decodeURIComponent(consent?.value ?? '{}'))).toMatchObject({ essential: true, analytics: false, v: 1 });

        await page.reload();
        await expect(banner).toBeHidden();
    });

    test('preferences dialog toggles analytics and traps focus', async ({ page }) => {
        await page.goto('/cookie-policy');
        await page.getByRole('button', { name: 'Open cookie settings' }).click();
        const dialog = page.getByRole('dialog', { name: 'Cookie preferences' });
        await expect(dialog).toBeVisible();

        const toggle = dialog.getByRole('switch', { name: 'Analytics' });
        await expect(toggle).toHaveAttribute('aria-checked', 'false');
        await toggle.click();
        await expect(toggle).toHaveAttribute('aria-checked', 'true');
        await dialog.getByRole('button', { name: 'Save preferences' }).click();
        await expect(dialog).toBeHidden();

        const cookie = (await page.context().cookies()).find((c) => c.name === 'ht_consent');
        expect(decodeURIComponent(cookie?.value ?? '')).toContain('"analytics":true');
    });
});

test.describe('homepage', () => {
    test('hero carousel navigates and announces slides', async ({ page }) => {
        await page.goto('/');
        await acceptCookies(page);
        const carousel = page.locator('[aria-roledescription="carousel"]');
        await expect(carousel.getByRole('heading', { level: 1 })).toHaveText('Building Sustainable Livestock and Palm Production');

        await carousel.getByRole('button', { name: 'Next slide' }).click();
        await expect(carousel.locator('[aria-live="polite"]')).toHaveText('Slide 2 of 3');
        await expect(carousel.getByRole('button', { name: 'Show slide 2' })).toHaveAttribute('aria-current', 'true');

        await carousel.getByRole('button', { name: 'Previous slide' }).click();
        await expect(carousel.locator('[aria-live="polite"]')).toHaveText('Slide 1 of 3');
    });

    test('primary calls to action lead to working pages', async ({ page }) => {
        await page.goto('/');
        await acceptCookies(page);
        await page.getByRole('link', { name: 'Become a Partner' }).first().click();
        await expect(page).toHaveURL(/\/partners$/);
        await expect(page.getByRole('heading', { level: 1 })).toContainText('Strategic Partnerships');
    });
});

test.describe('navigation', () => {
    test('mobile drawer opens, traps focus and closes with Escape', async ({ page }) => {
        test.skip(!isMobile(page), 'Drawer is the <1280px navigation');
        await page.goto('/about');
        await acceptCookies(page);

        const trigger = page.getByRole('button', { name: 'Open menu' });
        await trigger.click();
        const drawer = page.getByRole('dialog', { name: 'Menu' });
        await expect(drawer).toBeVisible();
        await expect(drawer.getByRole('link', { name: 'About Us' })).toHaveAttribute('aria-current', 'page');

        for (let i = 0; i < 15; i++) {
            await page.keyboard.press('Tab');
        }
        const focusInside = await drawer.evaluate((el) => el.contains(document.activeElement));
        expect(focusInside).toBe(true);

        await page.keyboard.press('Escape');
        await expect(drawer).toBeHidden();
    });

    test('desktop nav highlights the current section', async ({ page }) => {
        test.skip(isMobile(page), 'Desktop navigation');
        await page.goto('/products/table-eggs');
        await expect(page.getByRole('navigation', { name: 'Main' }).getByRole('link', { name: 'Products' })).toHaveAttribute('aria-current', 'page');
    });
});

test.describe('gallery', () => {
    test('filters by category and opens a keyboard-operable lightbox', async ({ page }) => {
        await page.goto('/gallery');
        await acceptCookies(page);
        const tiles = page.locator('main ul li a[aria-label^="Open photo"]');
        const total = await tiles.count();
        expect(total).toBeGreaterThan(10);

        await page.getByRole('group', { name: 'Filter photos by category' }).getByRole('link', { name: 'Poultry', exact: true }).click();
        await expect(page).toHaveURL(/category=poultry/);
        await expect.poll(async () => tiles.filter({ visible: true }).count()).toBeLessThan(total);

        await tiles.filter({ visible: true }).first().click();
        const lightbox = page.getByRole('dialog');
        await expect(lightbox).toBeVisible();
        await expect(lightbox).toContainText('Poultry · 1 of');

        await page.keyboard.press('ArrowRight');
        await expect(lightbox).toContainText('Poultry · 2 of');
        await page.keyboard.press('Escape');
        await expect(lightbox).toBeHidden();
    });
});

test.describe('products', () => {
    test('category chips filter server-side and update the URL', async ({ page }) => {
        await page.goto('/products');
        await acceptCookies(page);
        await page.getByRole('button', { name: isMobile(page) ? 'Palm' : 'Palm Products' }).click();
        await expect(page).toHaveURL(/category=palm-products/);
        await expect(page.getByRole('heading', { name: 'Palm Oil' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Table Eggs' })).toHaveCount(0);
    });

    test('notify me dialog subscribes for an out-of-season product', async ({ page }) => {
        await page.goto('/products?availability=out_of_season');
        await acceptCookies(page);
        await page.getByRole('button', { name: /Notify me/ }).first().click();
        const dialog = page.getByRole('dialog', { name: /Notify me about/ });
        await dialog.getByLabel('Email').fill(`buyer+${Date.now()}@example.com`);
        await dialog.getByRole('button', { name: 'Notify me' }).click();
        await expect(dialog.getByText('You’re on the list')).toBeVisible();
    });
});

test.describe('enquiry forms', () => {
    test('contact form shows inline errors, then a confirmation with a reference', async ({ page }) => {
        await page.goto('/contact');
        await acceptCookies(page);
        const form = page.locator('form[aria-labelledby="contact-form-title"]');

        await form.getByRole('button', { name: 'Send Message' }).click();
        await expect(form.getByText('Enter your full name.')).toBeVisible();
        await expect(form.getByLabel('Email')).toHaveAttribute('aria-invalid', 'true');

        await form.getByLabel('Full name').fill('Playwright Buyer');
        await form.getByLabel('Phone').fill('0810 780 7012');
        await form.getByLabel('Email').fill('playwright@example.com');
        if (isMobile(page)) {
            await form.getByLabel('Area of interest').selectOption('eggs');
        } else {
            await form.locator('label.chip', { hasText: /^\s*Eggs\s*$/ }).click();
        }
        await form.getByLabel('Message').fill('Please send prices for 50 crates a week.');
        await page.waitForTimeout(2500); // minimum human fill time
        await form.getByRole('button', { name: 'Send Message' }).click();

        const success = page.getByRole('status').filter({ hasText: 'your message has been sent' });
        await expect(success).toBeVisible();
        await expect(success).toContainText(/HTC-\d{4}-[A-Z0-9]{5}/);
    });

    test('quote wizard validates each mobile step and submits', async ({ page }) => {
        test.skip(!isMobile(page), 'Stepped wizard is the mobile layout');
        await page.goto('/quote?product=table-eggs');
        await acceptCookies(page);
        await expect(page.getByText('Step 1 of 3 — Products')).toBeVisible();

        await page.getByRole('button', { name: 'Continue', exact: true }).click();
        await expect(page.getByText('Enter a quantity.')).toBeVisible();

        await page.getByLabel('Quantity').fill('40');
        await page.getByRole('button', { name: 'Continue', exact: true }).click();
        await expect(page.getByText('Step 2 of 3 — Delivery')).toBeVisible();

        await page.getByLabel('Preferred delivery location').fill('Abeokuta, Ogun');
        const inAWeek = new Date(Date.now() + 7 * 864e5).toISOString().slice(0, 10);
        await page.getByLabel('Required by').fill(inAWeek);
        await page.getByRole('button', { name: 'Continue', exact: true }).click();
        await expect(page.getByText('Step 3 of 3 — Your details')).toBeVisible();

        await page.getByLabel('Full name').fill('Playwright Quote');
        await page.getByLabel('Phone').fill('+234 805 046 0470');
        await page.getByRole('textbox', { name: 'Email (required)', exact: true }).fill('quote@example.com');
        await page.waitForTimeout(500);
        await page.getByRole('button', { name: 'Submit Quote Request' }).first().click();

        await expect(page.getByText('your quote request has been sent')).toBeVisible();
        await expect(page.getByRole('status')).toContainText(/HTQ-\d{4}-/);
    });

    test('desktop quote form shows all sections and a live summary', async ({ page }) => {
        test.skip(isMobile(page), 'Desktop layout');
        await page.goto('/quote?product=table-eggs');
        await acceptCookies(page);
        for (const heading of ['1 · What do you need?', '2 · Delivery', '3 · Your details']) {
            await expect(page.getByRole('heading', { name: heading })).toBeVisible();
        }
        await page.getByLabel('Quantity').fill('400');
        await page.getByLabel('Quantity').blur();
        await expect(page.getByRole('complementary', { name: 'Your request' })).toContainText('400 crates / week');
    });
});

test.describe('responsive layout', () => {
    const paths = ['/', '/about', '/livestock', '/poultry', '/palm-oil', '/products', '/products/table-eggs', '/gallery', '/partners', '/contact', '/quote', '/sustainability', '/news'];

    for (const width of [390, 768, 1280, 1440]) {
        test(`no horizontal overflow at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            for (const path of paths) {
                await page.goto(path);
                const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
                expect(overflow, `${path} overflows by ${overflow}px at ${width}px`).toBeLessThanOrEqual(0);
            }
        });
    }
});
