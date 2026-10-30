<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** "Notify me" for out-of-season products: one email when it becomes available again. */
class StockAlertForm extends Component
{
    #[Locked]
    public int $productId;

    public string $productName = '';

    public string $email = '';

    public string $website = '';

    public bool $subscribed = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $this->productName = $product->name;
    }

    public function subscribe(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'max:190', config('enquiries.verify_email_dns') ? 'email:rfc,dns' : 'email:rfc'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address, like name@company.com.',
        ]);

        if ($this->website !== '') {
            return;
        }

        $key = 'stock-alert|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('email', 'Too many requests from your connection. Please try again later.');

            return;
        }
        RateLimiter::hit($key, 3600);

        $product = Product::query()->published()->findOrFail($this->productId);
        $email = mb_strtolower(trim($validated['email']));

        try {
            $alert = StockAlert::firstOrCreate(['product_id' => $product->id, 'email' => $email]);
        } catch (UniqueConstraintViolationException) {
            $alert = StockAlert::query()->where(['product_id' => $product->id, 'email' => $email])->firstOrFail();
        }

        if ($alert->notified_at !== null) {
            $alert->update(['notified_at' => null]);
        }

        $this->subscribed = true;
        $this->reset('email');
    }

    public function render(): View
    {
        return view('livewire.stock-alert-form');
    }
}
