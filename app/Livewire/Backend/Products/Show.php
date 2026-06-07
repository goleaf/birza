<?php

namespace App\Livewire\Backend\Products;

use App\Models\AuditLog;
use App\Models\Product;
use App\Support\SafeMarkdown;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public Product $product;

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);

        $this->product = $product->load(['category', 'seller', 'attributeValues.attribute', 'images']);
    }

    public function render()
    {
        $description = (string) $this->product->getTranslation('description', app()->getLocale());

        return view('backend.products.show', [
            'auditLogs' => AuditLog::query()
                ->entity($this->product)
                ->with('actor')
                ->latest('created_at')
                ->limit(10)
                ->get(),
            'product' => $this->product,
            'descriptionHtml' => SafeMarkdown::render($description),
            'hasDescription' => $description !== '',
        ]);
    }
}
