<?php

namespace Tests\Feature;

use App\Contracts\CaptchaVerifier;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Captcha\NullCaptchaVerifier;
use App\Services\Captcha\TurnstileVerifier;
use App\Services\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationAdaptersTest extends TestCase
{
    use RefreshDatabase;

    private function turnstile(): TurnstileVerifier
    {
        return new TurnstileVerifier(app(Factory::class), 'site', 'secret', 'https://turnstile.test/verify');
    }

    public function test_captcha_binding_depends_on_configuration(): void
    {
        $this->assertInstanceOf(NullCaptchaVerifier::class, app(CaptchaVerifier::class));

        config(['enquiries.captcha' => 'turnstile', 'services.turnstile.site_key' => 'k', 'services.turnstile.secret_key' => 's']);
        $this->app->forgetInstance(CaptchaVerifier::class);
        $this->assertInstanceOf(TurnstileVerifier::class, app(CaptchaVerifier::class));

        // Missing secret falls back to no captcha instead of blocking every form.
        config(['services.turnstile.secret_key' => '']);
        $this->app->forgetInstance(CaptchaVerifier::class);
        $this->assertInstanceOf(NullCaptchaVerifier::class, app(CaptchaVerifier::class));
    }

    public function test_turnstile_accepts_valid_and_rejects_invalid_tokens(): void
    {
        Http::fake(['turnstile.test/*' => Http::sequence()
            ->push(['success' => true])
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        $this->assertTrue($this->turnstile()->verify('token', '1.2.3.4'));
        $this->assertFalse($this->turnstile()->verify('token'));
        $this->assertFalse($this->turnstile()->verify(''));

        Http::assertSent(fn (Request $request) => $request['secret'] === 'secret' && ($request->data()['remoteip'] ?? null) === '1.2.3.4');
        Http::assertSentCount(2);
    }

    public function test_turnstile_fails_open_when_cloudflare_is_unavailable(): void
    {
        Http::fake(['turnstile.test/*' => Http::response('oops', 503)]);
        $this->assertTrue($this->turnstile()->verify('token'));

        Http::fake(['turnstile.test/*' => fn () => throw new ConnectionException('timeout')]);
        $this->assertTrue($this->turnstile()->verify('token'));
    }

    public function test_site_settings_prefer_saved_values_and_refresh_on_save(): void
    {
        $settings = app(SiteSettings::class);
        $this->assertSame('+234 810 780 7012', $settings->get('phone_primary'));
        $this->assertSame('https://wa.me/2348107807012?text=Hi%20there', $settings->whatsappUrl('Hi there'));
        $this->assertSame('tel:+2348107807012', $settings->telHref('+234 810 780 7012'));

        Setting::create(['key' => 'phone_primary', 'value' => '+234 800 000 0000']);
        $this->assertSame('+234 800 000 0000', app(SiteSettings::class)->get('phone_primary'));

        Setting::updateOrCreate(['key' => 'enquiry_recipients'], ['value' => 'a@example.com, not-an-email, b@example.com']);
        $this->assertSame(['a@example.com', 'b@example.com'], app(SiteSettings::class)->enquiryRecipients());
    }

    public function test_enquiry_references_are_unique_and_typed(): void
    {
        $references = collect(range(1, 25))->map(fn () => Enquiry::factory()->quote()->create()->reference);

        $this->assertCount(25, $references->unique());
        $references->each(fn (string $ref) => $this->assertMatchesRegularExpression('/^HTQ-\d{4}-[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{5}$/', $ref));
        $this->assertStringStartsWith('HTP-', Enquiry::generateReference(EnquiryType::Partnership));
    }
}
