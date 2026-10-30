<?php

namespace App\Livewire;

use App\Enums\ProductAvailability;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Product catalogue with server-side chip filter, search and availability filter (handoff §05). */
class ProductIndex extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;

    #[Url(as: 'category', except: '')]
    public string $category = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'availability', except: '')]
    public string $availability = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['category', 'search', 'availability'], true)) {
            $this->resetPage();
        }
    }

    public function setCategory(string $slug): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('category', 'search', 'availability');
        $this->resetPage();
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ProductCategory::query()->ordered()->get();
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $search = trim(mb_substr($this->search, 0, 80));
        $availability = ProductAvailability::tryFrom($this->availability);

        return Product::query()
            ->published()
            ->with(['category', 'media'])
            ->when($this->category !== '', fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $category) => $category->where('slug', $this->category),
            ))
            ->when($availability, fn (Builder $query) => $query->where('availability', $availability))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
                $query->where('name', 'like', $term)->orWhere('summary', 'like', $term);
            }))
            ->ordered()
            ->paginate(self::PER_PAGE);
    }

    public function hasFilters(): bool
    {
        return $this->category !== '' || $this->search !== '' || $this->availability !== '';
    }

    public function render(): View
    {
        return view('livewire.product-index', [
            'availabilities' => ProductAvailability::cases(),
        ]);
    }
}
