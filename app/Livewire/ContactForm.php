<?php

namespace App\Livewire;

use App\Actions\Enquiries\RecordEnquiry;
use App\Enums\AreaOfInterest;
use App\Enums\EnquiryType;
use App\Livewire\Concerns\ProtectsAgainstSpam;
use App\Models\Product;
use App\Rules\PhoneNumber;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

/** "Send a message" form on /contact (handoff §10). */
class ContactForm extends Component
{
    use ProtectsAgainstSpam;

    public string $name = '';

    public string $organisation = '';

    public string $phone = '';

    public string $email = '';

    public string $area = '';

    public string $message = '';

    public bool $submitted = false;

    public ?string $reference = null;

    public function mount(?string $interest = null, ?string $product = null): void
    {
        $this->area = AreaOfInterest::tryFrom((string) $interest)->value ?? '';

        if ($product && $model = Product::query()->published()->where('slug', $product)->first()) {
            $this->message = "I would like to place an order for {$model->name}.";
            $this->area = $this->area ?: AreaOfInterest::CommercialSupply->value;
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'phone' => $this->phoneRules(),
            'email' => $this->emailRules(),
            'area' => ['required', Rule::enum(AreaOfInterest::class)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Enter your full name.',
            'phone.required' => 'Enter a phone number we can call you on.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address, like name@company.com.',
            'area.required' => 'Choose the area your message is about.',
            'message.required' => 'Tell us how we can help.',
            'message.min' => 'Please give us a little more detail (at least 10 characters).',
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
                'type' => EnquiryType::Contact,
                'idempotency_key' => $this->submissionKey,
                'name' => $validated['name'],
                'organisation' => $validated['organisation'],
                'email' => $validated['email'],
                'phone' => PhoneNumber::normalize($validated['phone']),
                'subject' => $validated['area'],
                'message' => $validated['message'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('form', 'Sorry, your message could not be sent. Please try again, or call us on '.$settings->get('phone_primary').'.');

            return;
        }

        $this->reference = $enquiry->reference;
        $this->submitted = true;
        $this->reset('name', 'organisation', 'phone', 'email', 'area', 'message');
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
        return 'enquiry:contact';
    }

    public function render(): View
    {
        return view('livewire.contact-form', [
            'areas' => AreaOfInterest::cases(),
        ]);
    }
}
