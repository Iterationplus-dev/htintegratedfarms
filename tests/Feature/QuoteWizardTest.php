<?php

namespace Tests\Feature;

use App\Enums\EnquiryType;
use App\Enums\ProductAvailability;
use App\Enums\QuoteFrequency;
use App\Livewire\QuoteWizard;
use App\Models\Enquiry;
use App\Models\QuoteDraft;
use App\Notifications\EnquiryReceivedConfirmation;
use App\Notifications\NewEnquiryReceived;
use App\Notifications\QuoteDraftSaved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        RateLimiter::clear('enquiry:quote|127.0.0.1');
        RateLimiter::clear('quote-draft|127.0.0.1');
    }

    private function completed(Testable $component, int $productId): Testable
    {
        return $component
            ->set('items.0.product_id', $productId)
            ->set('items.0.quantity', '400')
            ->set('items.0.frequency', 'weekly')
            ->set('deliveryLocation', 'Ikeja, Lagos')
            ->set('requiredBy', now()->addWeek()->toDateString())
            ->set('name', 'Chidi Eze')
            ->set('phone', '+234 805 046 0470')
            ->set('email', 'chidi@example.com');
    }

    public function test_product_query_preselects_the_first_line(): void
    {
        $eggs = $this->makeProduct();

        Livewire::test(QuoteWizard::class, ['product' => $eggs->slug])
            ->assertSet('items.0.product_id', $eggs->id)
            ->assertSee('Table Eggs');
    }

    public function test_mobile_steps_validate_before_advancing(): void
    {
        $eggs = $this->makeProduct();

        Livewire::test(QuoteWizard::class)
            ->call('next')
            ->assertHasErrors(['items.0.product_id' => 'required', 'items.0.quantity' => 'required'])
            ->assertSet('step', 1)
            ->set('items.0.product_id', $eggs->id)
            ->set('items.0.quantity', '0')
            ->call('next')
            ->assertHasErrors(['items.0.quantity' => 'min'])
            ->set('items.0.quantity', '50')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 2)
            ->set('requiredBy', now()->subDay()->toDateString())
            ->set('deliveryLocation', 'Abeokuta')
            ->call('next')
            ->assertHasErrors(['requiredBy' => 'after_or_equal'])
            ->assertSet('step', 2)
            ->call('back')
            ->assertSet('step', 1);
    }

    public function test_submitting_creates_a_quote_with_line_items_and_notifies(): void
    {
        $eggs = $this->makeProduct();
        $broilers = $this->makeProduct('Broiler Chickens', attributes: ['unit' => 'birds']);

        $component = $this->completed(Livewire::test(QuoteWizard::class), $eggs->id)
            ->call('addItem')
            ->set('items.1.product_id', $broilers->id)
            ->set('items.1.quantity', '1000')
            ->set('items.1.frequency', 'monthly');

        $component->assertSee('400 crates / week')->assertSee('1,000 birds / month');
        $this->travel(5)->seconds();

        $component->call('submit')->assertHasNoErrors()->assertSet('submitted', true)->assertSet('step', 1);

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryType::Quote, $enquiry->type);
        $this->assertSame('Ikeja, Lagos', $enquiry->payload['delivery_location']);
        $this->assertSame('+2348050460470', $enquiry->phone);
        $this->assertCount(2, $enquiry->items);
        $first = $enquiry->items->firstWhere('product_id', $eggs->id);
        $this->assertSame('Table Eggs', $first->product_name);
        $this->assertSame(400, $first->quantity);
        $this->assertSame('crates', $first->unit);
        $this->assertSame(QuoteFrequency::Weekly, $first->frequency);

        Notification::assertSentOnDemand(NewEnquiryReceived::class);
        Notification::assertSentOnDemand(EnquiryReceivedConfirmation::class);
    }

    public function test_submit_jumps_to_the_first_step_with_errors(): void
    {
        $eggs = $this->makeProduct();
        $this->travel(5)->seconds();

        $this->completed(Livewire::test(QuoteWizard::class), $eggs->id)
            ->set('step', 3)
            ->set('deliveryLocation', '')
            ->call('submit')
            ->assertHasErrors('deliveryLocation')
            ->assertSet('step', 2);

        $this->assertSame(0, Enquiry::count());
    }

    public function test_out_of_season_and_unpublished_products_cannot_be_quoted(): void
    {
        $offSeason = $this->makeProduct('Processed Poultry', ProductAvailability::OutOfSeason);
        $hidden = $this->makeProduct('Secret', attributes: ['is_published' => false]);

        Livewire::test(QuoteWizard::class)
            ->set('items.0.product_id', $offSeason->id)->set('items.0.quantity', '5')
            ->call('next')->assertHasErrors(['items.0.product_id' => 'exists']);

        Livewire::test(QuoteWizard::class)
            ->set('items.0.product_id', $hidden->id)->set('items.0.quantity', '5')
            ->call('next')->assertHasErrors(['items.0.product_id' => 'exists']);
    }

    public function test_duplicate_products_are_rejected_and_lines_can_be_removed(): void
    {
        $eggs = $this->makeProduct();

        Livewire::test(QuoteWizard::class)
            ->set('items.0.product_id', $eggs->id)->set('items.0.quantity', '5')
            ->call('addItem')
            ->set('items.1.product_id', $eggs->id)->set('items.1.quantity', '6')
            ->call('next')
            ->assertHasErrors(['items.1.product_id' => 'distinct'])
            ->call('removeItem', 1)
            ->assertCount('items', 1)
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 2);
    }

    public function test_save_and_continue_later_emails_a_signed_link_that_restores_the_form(): void
    {
        $eggs = $this->makeProduct();

        Livewire::test(QuoteWizard::class)
            ->set('items.0.product_id', $eggs->id)->set('items.0.quantity', '120')
            ->set('deliveryLocation', 'Ibadan, Oyo')
            ->set('step', 2)
            ->set('draftEmail', 'buyer@example.com')
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertSet('draftSavedTo', 'buyer@example.com')
            ->assertSee('We’ve emailed a link');

        $draft = QuoteDraft::sole();
        $this->assertSame(0, Enquiry::count(), 'Saving a draft must not create an enquiry.');

        $resumeUrl = null;
        Notification::assertSentOnDemand(QuoteDraftSaved::class, function (QuoteDraftSaved $notification) use ($draft, &$resumeUrl): bool {
            $resumeUrl = $notification->resumeUrl();

            return $notification->draft->is($draft);
        });

        $this->get($resumeUrl)->assertRedirect(route('quote'));
        $this->assertNotNull($draft->fresh()->resumed_at);

        Livewire::withQueryParams([])->test(QuoteWizard::class)
            ->assertSet('items.0.product_id', $eggs->id)
            ->assertSet('items.0.quantity', '120')
            ->assertSet('deliveryLocation', 'Ibadan, Oyo')
            ->assertSet('step', 2);
    }

    public function test_resume_link_requires_a_valid_signature_and_unexpired_draft(): void
    {
        $draft = QuoteDraft::factory()->create();
        $this->get(route('quote.resume', ['draft' => $draft->uuid]))->assertForbidden();

        $expired = QuoteDraft::factory()->expired()->create();
        $url = URL::signedRoute('quote.resume', ['draft' => $expired->uuid]);
        $this->get($url)->assertRedirect(route('quote'))->assertSessionHas('status', fn (string $s) => str_contains($s, 'expired'));
        $this->assertNull($expired->fresh()->resumed_at);
    }

    public function test_draft_email_is_required(): void
    {
        Livewire::test(QuoteWizard::class)->call('saveDraft')->assertHasErrors(['draftEmail' => 'required']);
        $this->assertSame(0, QuoteDraft::count());
    }

    public function test_expired_drafts_are_pruned(): void
    {
        QuoteDraft::factory()->expired()->create();
        $live = QuoteDraft::factory()->create();

        $this->artisan('model:prune', ['--model' => [QuoteDraft::class]])->assertSuccessful();

        $this->assertSame([$live->id], QuoteDraft::pluck('id')->all());
    }
}
