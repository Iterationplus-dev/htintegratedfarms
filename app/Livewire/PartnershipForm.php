<?php

namespace App\Livewire;

use App\Actions\Enquiries\RecordEnquiry;
use App\Enums\EnquiryType;
use App\Enums\PartnershipType;
use App\Livewire\Concerns\ProtectsAgainstSpam;
use App\Rules\PhoneNumber;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

/** "Become a Partner" form on /partners (handoff §09). */
class PartnershipForm extends Component
{
    use ProtectsAgainstSpam;

    public string $organisation = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $partnershipType = '';

    public string $proposal = '';

    public bool $submitted = false;

    public ?string $reference = null;

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'organisation' => ['required', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:120'],
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(required: false),
            'partnershipType' => ['required', Rule::enum(PartnershipType::class)],
            'proposal' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'organisation.required' => 'Enter your organisation’s name.',
            'name.required' => 'Enter the name of the contact person.',
            'email.required' => 'Enter an email address for replies.',
            'email.email' => 'Enter a valid email address, like name@company.com.',
            'partnershipType.required' => 'Select the type of partnership.',
            'proposal.required' => 'Describe what you are proposing.',
            'proposal.min' => 'Please describe your proposal in a little more detail (at least 20 characters).',
        ];
    }

    public function submit(RecordEnquiry $recordEnquiry, SiteSettings $settings): void
    {
        $this->resetErrorBag('form');
        $validated = $this->validate();

        if (! $this->passesSpamChecks()) {
            return;
        }

        try {
            $enquiry = $recordEnquiry->handle([
                'type' => EnquiryType::Partnership,
                'idempotency_key' => $this->submissionKey,
                'name' => $validated['name'],
                'organisation' => $validated['organisation'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ? PhoneNumber::normalize($validated['phone']) : null,
                'subject' => $validated['partnershipType'],
                'message' => $validated['proposal'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('form', 'Sorry, your inquiry could not be sent. Please try again, or email '.$settings->get('email_partners').'.');

            return;
        }

        $this->reference = $enquiry->reference;
        $this->submitted = true;
        $this->reset('organisation', 'name', 'email', 'phone', 'partnershipType', 'proposal');
        $this->resetSpamState();
        $this->dispatch('enquiry-submitted');
    }

    public function startAnother(): void
    {
        $this->submitted = false;
        $this->reference = null;
    }

    protected function rateLimitKey(): string
    {
        return 'enquiry:partnership';
    }

    public function render(): View
    {
        return view('livewire.partnership-form', [
            'types' => PartnershipType::cases(),
        ]);
    }
}
