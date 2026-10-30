<?php

namespace App\Livewire;

use App\Actions\Enquiries\RecordEnquiry;
use App\Actions\Quotes\SaveQuoteDraft;
use App\Enums\EnquiryType;
use App\Enums\ProductAvailability;
use App\Enums\QuoteFrequency;
use App\Livewire\Concerns\ProtectsAgainstSpam;
use App\Models\Product;
use App\Models\QuoteDraft;
use App\Rules\PhoneNumber;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

/**
 * Three-step "Request a Quote" form (handoff §11).
 *
 * Mobile (<1280px) is a stepped wizard: `$step` selects the visible section and **Continue**
 * validates that step only. Desktop shows all three sections at once (as designed) and
 * **Submit** validates everything, jumping `$step` to the first section with errors.
 *
 * @property-read Collection<int, Product> $products
 * @property-read array<int, bool> $completedSteps
 * @property-read int $firstIncompleteStep
 * @property-read list<array{name: string, detail: string, pending: bool}> $summary
 */
class QuoteWizard extends Component
{
    use ProtectsAgainstSpam;

    public const STEPS = [1 => 'Products', 2 => 'Delivery', 3 => 'Your details'];

    public const MAX_ITEMS = 10;

    /** @var list<array{product_id: int|string|null, quantity: int|string|null, frequency: string}> */
    public array $items = [];

    public string $deliveryLocation = '';

    public string $requiredBy = '';

    public string $name = '';

    public string $organisation = '';

    public string $phone = '';

    public string $email = '';

    public int $step = 1;

    public bool $submitted = false;

    public ?string $reference = null;

    public string $draftEmail = '';

    public ?string $draftSavedTo = null;

    public function mount(?string $product = null): void
    {
        $draftUuid = session()->pull('quote_draft');
        $draft = $draftUuid ? QuoteDraft::query()->where('uuid', $draftUuid)->first() : null;

        if ($draft && ! $draft->isExpired()) {
            $this->restore($draft->data);
            $this->draftEmail = $draft->email;

            return;
        }

        $productId = $product ? $this->products->firstWhere('slug', $product)?->id : null;
        $this->items = [$this->blankItem($productId)];
    }

    /**
     * Published products that can currently be quoted, keyed by id.
     *
     * @return Collection<int, Product>
     */
    #[Computed(persist: false)]
    public function products(): Collection
    {
        return Product::query()
            ->published()
            ->where('availability', '!=', ProductAvailability::OutOfSeason)
            ->with(['category', 'media'])
            ->ordered()
            ->get()
            ->keyBy('id');
    }

    public function addItem(): void
    {
        if (count($this->items) < self::MAX_ITEMS) {
            $this->items[] = $this->blankItem();
        }
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1 && isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->resetValidation();
        }
    }

    public function next(): void
    {
        $this->validate($this->stepRules($this->step));
        $this->step = min($this->step + 1, count(self::STEPS));
    }

    public function back(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
        }
    }

    public function submit(RecordEnquiry $recordEnquiry, SiteSettings $settings): void
    {
        $this->resetErrorBag('form');

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->step = $this->firstStepWithErrors(array_keys($exception->errors()));

            throw $exception;
        }

        if (! $this->passesSpamChecks()) {
            return;
        }

        $products = $this->products;
        $items = array_map(function (array $item) use ($products): array {
            /** @var Product $product */
            $product = $products->get((int) $item['product_id']);

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => (int) $item['quantity'],
                'unit' => $product->unit,
                'frequency' => QuoteFrequency::from($item['frequency']),
            ];
        }, $this->items);

        try {
            $enquiry = $recordEnquiry->handle([
                'type' => EnquiryType::Quote,
                'idempotency_key' => $this->submissionKey,
                'name' => trim($this->name),
                'organisation' => $this->organisation,
                'email' => trim($this->email),
                'phone' => PhoneNumber::normalize($this->phone),
                'payload' => [
                    'delivery_location' => trim($this->deliveryLocation),
                    'required_by' => $this->requiredBy,
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ], $items);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('form', 'Sorry, your quote request could not be sent. Please try again, or call us on '.$settings->get('phone_primary').'.');

            return;
        }

        $this->reference = $enquiry->reference;
        $this->submitted = true;
        $this->reset('deliveryLocation', 'requiredBy', 'name', 'organisation', 'phone', 'email', 'draftSavedTo');
        $this->items = [$this->blankItem()];
        $this->step = 1;
        $this->resetSpamState();
        $this->dispatch('enquiry-submitted');
    }

    public function saveDraft(SaveQuoteDraft $saveQuoteDraft): void
    {
        if ($this->draftEmail === '' && $this->email !== '') {
            $this->draftEmail = $this->email;
        }

        $this->validateOnly('draftEmail', ['draftEmail' => $this->emailRules()], [
            'draftEmail.required' => 'Enter the email address we should send the link to.',
            'draftEmail.email' => 'Enter a valid email address, like name@company.com.',
        ]);

        $key = 'quote-draft|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('draftEmail', 'You have saved several drafts recently. Please try again later.');

            return;
        }
        RateLimiter::hit($key, 600);

        $saveQuoteDraft->handle(trim($this->draftEmail), $this->draftState());
        $this->draftSavedTo = trim($this->draftEmail);
    }

    public function startAnother(): void
    {
        $this->submitted = false;
        $this->reference = null;
    }

    /**
     * Step status for the progress indicators.
     *
     * @return array<int, bool>
     */
    #[Computed]
    public function completedSteps(): array
    {
        $completed = [];
        foreach (array_keys(self::STEPS) as $step) {
            $completed[$step] = Validator::make($this->formData(), $this->stepRules($step, strict: false))->passes();
        }

        return $completed;
    }

    /** First step that is not yet complete; drives the desktop indicator. */
    #[Computed]
    public function firstIncompleteStep(): int
    {
        foreach ($this->completedSteps as $step => $done) {
            if (! $done) {
                return $step;
            }
        }

        return count(self::STEPS);
    }

    /**
     * "Your request" summary lines.
     *
     * @return list<array{name: string, detail: string, pending: bool}>
     */
    #[Computed]
    public function summary(): array
    {
        $lines = [];
        foreach ($this->items as $item) {
            $product = $item['product_id'] ? $this->products->get((int) $item['product_id']) : null;
            if (! $product) {
                continue;
            }
            $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $frequency = QuoteFrequency::tryFrom($item['frequency']);
            $lines[] = $quantity === false
                ? ['name' => $product->name, 'detail' => 'Quantity pending', 'pending' => true]
                : ['name' => $product->name, 'detail' => trim(number_format($quantity).' '.$product->unit).' '.($frequency?->summarySuffix() ?? ''), 'pending' => false];
        }

        return $lines;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return array_merge($this->stepRules(1), $this->stepRules(2), $this->stepRules(3));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function stepRules(int $step, bool $strict = true): array
    {
        return match ($step) {
            1 => [
                'items' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
                'items.*.product_id' => [
                    'required', 'integer', 'distinct',
                    Rule::exists('products', 'id')
                        ->where('is_published', true)
                        ->whereNot('availability', ProductAvailability::OutOfSeason->value),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
                'items.*.frequency' => ['required', Rule::enum(QuoteFrequency::class)],
            ],
            2 => [
                'deliveryLocation' => ['required', 'string', 'max:160'],
                'requiredBy' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before:'.now()->addYears(2)->toDateString()],
            ],
            3 => [
                'name' => ['required', 'string', 'max:120'],
                'organisation' => ['nullable', 'string', 'max:160'],
                'phone' => $this->phoneRules(),
                'email' => $strict ? $this->emailRules() : ['required', 'email:rfc', 'max:190'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'items.*.product_id.required' => 'Choose a product.',
            'items.*.product_id.exists' => 'This product is not available to quote right now.',
            'items.*.product_id.distinct' => 'This product is already in your request.',
            'items.*.quantity.required' => 'Enter a quantity.',
            'items.*.quantity.integer' => 'Enter a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.frequency.required' => 'Choose how often you need it.',
            'deliveryLocation.required' => 'Enter the city and state for delivery.',
            'requiredBy.required' => 'Choose the date you need delivery by.',
            'requiredBy.date_format' => 'Enter the date as dd/mm/yyyy.',
            'requiredBy.after_or_equal' => 'The date must be today or later.',
            'requiredBy.before' => 'Choose a date within the next two years.',
            'name.required' => 'Enter your full name.',
            'phone.required' => 'Enter a phone number we can call you on.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address, like name@company.com.',
        ];
    }

    protected function rateLimitKey(): string
    {
        return 'enquiry:quote';
    }

    /**
     * @param  list<string>  $keys
     */
    private function firstStepWithErrors(array $keys): int
    {
        foreach (array_keys(self::STEPS) as $step) {
            $patterns = array_keys($this->stepRules($step, strict: false));
            foreach ($keys as $key) {
                foreach ($patterns as $pattern) {
                    if ($key === $pattern || fnmatch($pattern, $key)) {
                        return $step;
                    }
                }
            }
        }

        return 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'items' => $this->items,
            'deliveryLocation' => $this->deliveryLocation,
            'requiredBy' => $this->requiredBy,
            'name' => $this->name,
            'organisation' => $this->organisation,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function draftState(): array
    {
        return $this->formData() + ['step' => $this->step];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function restore(array $data): void
    {
        $items = [];
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $productId = isset($item['product_id']) && is_numeric($item['product_id']) ? (int) $item['product_id'] : null;
            $items[] = [
                'product_id' => $productId && $this->products->has($productId) ? $productId : null,
                'quantity' => is_scalar($item['quantity'] ?? null) ? (string) $item['quantity'] : '',
                'frequency' => QuoteFrequency::tryFrom((string) ($item['frequency'] ?? ''))->value ?? QuoteFrequency::Weekly->value,
            ];
        }
        $this->items = array_slice($items, 0, self::MAX_ITEMS) ?: [$this->blankItem()];

        foreach (['deliveryLocation', 'requiredBy', 'name', 'organisation', 'phone', 'email'] as $field) {
            $this->{$field} = is_string($data[$field] ?? null) ? $data[$field] : '';
        }
        $this->step = max(1, min(count(self::STEPS), (int) ($data['step'] ?? 1)));
    }

    /**
     * @return array{product_id: int|null, quantity: string, frequency: string}
     */
    private function blankItem(?int $productId = null): array
    {
        return ['product_id' => $productId, 'quantity' => '', 'frequency' => QuoteFrequency::Weekly->value];
    }

    public function render(): View
    {
        return view('livewire.quote-wizard', [
            'steps' => self::STEPS,
            'frequencies' => QuoteFrequency::cases(),
        ]);
    }
}
