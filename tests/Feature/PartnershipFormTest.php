<?php

namespace Tests\Feature;

use App\Enums\EnquiryType;
use App\Livewire\PartnershipForm;
use App\Models\Enquiry;
use App\Notifications\NewEnquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PartnershipFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        RateLimiter::clear('enquiry:partnership|127.0.0.1');
    }

    public function test_partnership_inquiry_is_recorded(): void
    {
        $component = Livewire::test(PartnershipForm::class)
            ->set('organisation', 'Feedwell Nigeria')
            ->set('name', 'Tunde Bello')
            ->set('email', 'tunde@feedwell.example')
            ->set('phone', '+44 7760 892302')
            ->set('partnershipType', 'feed_supplier')
            ->set('proposal', 'We supply layer mash and would like to discuss a contract.');
        $this->travel(5)->seconds();

        $component->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryType::Partnership, $enquiry->type);
        $this->assertSame('Feed suppliers', $enquiry->subjectLabel());
        $this->assertSame('+447760892302', $enquiry->phone);
        $this->assertStringStartsWith('HTP-', $enquiry->reference);
        Notification::assertSentOnDemand(NewEnquiryReceived::class);
    }

    public function test_partnership_type_and_proposal_are_required(): void
    {
        Livewire::test(PartnershipForm::class)
            ->set('organisation', 'X')->set('name', 'Y')->set('email', 'y@example.com')
            ->set('partnershipType', 'pirates')->set('proposal', 'short')
            ->call('submit')
            ->assertHasErrors(['partnershipType', 'proposal' => 'min']);

        $this->assertSame(0, Enquiry::count());
    }
}
