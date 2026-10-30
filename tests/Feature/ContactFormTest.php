<?php

namespace Tests\Feature;

use App\Actions\Enquiries\RecordEnquiry;
use App\Contracts\CaptchaVerifier;
use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Livewire\ContactForm;
use App\Models\Enquiry;
use App\Notifications\EnquiryReceivedConfirmation;
use App\Notifications\NewEnquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        RateLimiter::clear('enquiry:contact|127.0.0.1');
    }

    private function filledForm(): Testable
    {
        $component = Livewire::test(ContactForm::class)
            ->set('name', 'Adaeze Okafor')
            ->set('organisation', 'Lagos Foods Ltd')
            ->set('phone', '0810 780 7012')
            ->set('email', 'adaeze@example.com')
            ->set('area', 'eggs')
            ->set('message', 'We need 200 crates of eggs every week.');
        $this->travel(5)->seconds();

        return $component;
    }

    public function test_valid_message_is_stored_and_notifications_are_queued(): void
    {
        $this->filledForm()
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertDispatched('enquiry-submitted')
            ->assertSee('your message has been sent');

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryType::Contact, $enquiry->type);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame('08107807012', $enquiry->phone);
        $this->assertSame('eggs', $enquiry->subject);
        $this->assertMatchesRegularExpression('/^HTC-\d{4}-[2-9A-Z]{5}$/', $enquiry->reference);
        $this->assertNotNull($enquiry->ip_hash);
        $this->assertNotSame('127.0.0.1', $enquiry->ip_hash);

        Notification::assertSentOnDemand(NewEnquiryReceived::class, function (NewEnquiryReceived $notification, array $channels, AnonymousNotifiable $notifiable) use ($enquiry): bool {
            return $notification->enquiry->is($enquiry) && $notifiable->routes['mail'] === ['sales@example.test'];
        });
        Notification::assertSentOnDemand(EnquiryReceivedConfirmation::class);
    }

    public function test_required_fields_and_formats_are_validated(): void
    {
        Livewire::test(ContactForm::class)
            ->call('submit')
            ->assertHasErrors(['name' => 'required', 'phone' => 'required', 'email' => 'required', 'area' => 'required', 'message' => 'required']);

        Livewire::test(ContactForm::class)
            ->set('phone', '12345')
            ->set('email', 'not-an-email')
            ->set('area', 'something-else')
            ->call('submit')
            ->assertHasErrors(['phone', 'email', 'area']);

        $this->assertSame(0, Enquiry::count());
        Notification::assertNothingSent();
    }

    public function test_invalid_fields_render_accessible_error_markup(): void
    {
        Livewire::test(ContactForm::class)
            ->call('submit')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('id="email-error"', false)
            ->assertSee('Enter your email address.');
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        $this->filledForm()->set('website', 'http://spam.example')->call('submit')->assertSet('submitted', false);

        $this->assertSame(0, Enquiry::count());
        Notification::assertNothingSent();
    }

    public function test_submissions_faster_than_a_human_are_rejected(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Bot')->set('phone', '08107807012')->set('email', 'bot@example.com')
            ->set('area', 'eggs')->set('message', 'Instant message from a script.')
            ->call('submit')
            ->assertHasErrors('form');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_repeated_submission_of_the_same_form_creates_one_enquiry(): void
    {
        $component = $this->filledForm();
        $key = $component->get('submissionKey');

        $component->call('submit');
        $this->assertSame(1, Enquiry::count());

        // A retried request carrying the original key returns the stored enquiry.
        $existing = app(RecordEnquiry::class)->handle([
            'type' => EnquiryType::Contact, 'idempotency_key' => $key, 'name' => 'Retry',
        ]);
        $this->assertSame(1, Enquiry::count());
        $this->assertSame('Adaeze Okafor', $existing->name);
        Notification::assertSentOnDemandTimes(NewEnquiryReceived::class, 1);

        // After success the form gets a fresh key, so a deliberate second message is accepted.
        $this->assertNotSame($key, $component->get('submissionKey'));
    }

    public function test_rate_limit_blocks_excess_submissions(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->filledForm()->call('submit')->assertHasNoErrors();
        }

        $this->filledForm()->call('submit')->assertHasErrors('form')->assertSee('Too many submissions');
        $this->assertSame(5, Enquiry::count());
    }

    public function test_failed_captcha_shows_an_error_and_stores_nothing(): void
    {
        $this->app->instance(CaptchaVerifier::class, new class implements CaptchaVerifier
        {
            public function enabled(): bool
            {
                return true;
            }

            public function siteKey(): ?string
            {
                return 'site-key';
            }

            public function verify(?string $token, ?string $ip = null): bool
            {
                return $token === 'good-token';
            }
        });

        $this->filledForm()->set('captchaToken', 'bad')->call('submit')
            ->assertHasErrors('captchaToken')->assertDispatched('captcha-reset');
        $this->assertSame(0, Enquiry::count());

        $this->filledForm()->set('captchaToken', 'good-token')->call('submit')->assertHasNoErrors();
        $this->assertSame(1, Enquiry::count());
    }

    public function test_query_string_prefills_area_and_product_message(): void
    {
        $product = $this->makeProduct('Palm Oil');

        Livewire::test(ContactForm::class, ['interest' => 'farm_visit'])->assertSet('area', 'farm_visit');
        Livewire::test(ContactForm::class, ['product' => $product->slug])
            ->assertSet('message', 'I would like to place an order for Palm Oil.')
            ->assertSet('area', 'commercial_supply');
        Livewire::test(ContactForm::class, ['interest' => '<script>'])->assertSet('area', '');
    }

    public function test_storage_failure_shows_a_retry_message(): void
    {
        $this->mock(RecordEnquiry::class)
            ->shouldReceive('handle')->andThrow(new \RuntimeException('DB down'));

        $this->filledForm()->call('submit')->assertHasErrors('form')->assertSet('submitted', false)
            ->assertSee('could not be sent');
    }
}
