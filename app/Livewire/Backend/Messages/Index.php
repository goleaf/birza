<?php

namespace App\Livewire\Backend\Messages;

use App\Models\Conversation;
use App\Models\Users\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(except: 'all')]
    public string $filter = 'all';

    public function mount(): void
    {
        abort_if(! Auth::guard('admin')->user() instanceof Admin, 403);

        $this->authorize('viewAny', Conversation::class);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $conversations = Conversation::query()
            ->summaryColumns()
            ->with([
                'buyer:id,name,company_name,email',
                'seller:id,name,company_name,email',
                'product:id,name',
                'order:id',
                'latestMessage:id,conversation_id,sender_id,sender_role,body,read_at,created_at',
                'latestMessage.senderBuyer:id,name,company_name',
                'latestMessage.senderSeller:id,name,company_name',
                'latestMessage.senderAdmin:id,name',
            ])
            ->when($this->filter === 'active', fn ($query) => $query->active())
            ->when($this->filter === 'closed', fn ($query) => $query->where('status', 'closed'))
            ->when($this->filter === 'blocked', fn ($query) => $query->where('status', 'blocked'))
            ->when($this->filter === 'product', fn ($query) => $query->whereNotNull('product_id'))
            ->when($this->filter === 'order', fn ($query) => $query->whereNotNull('order_id'))
            ->latestActivity()
            ->paginate(20)
            ->withQueryString();

        return view('livewire.backend.messages.index', [
            'conversations' => $conversations,
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function filterOptions(): array
    {
        return [
            ['id' => 'all', 'name' => __('messages.filters.all')],
            ['id' => 'active', 'name' => __('messages.status.active')],
            ['id' => 'closed', 'name' => __('messages.status.closed')],
            ['id' => 'blocked', 'name' => __('messages.status.blocked')],
            ['id' => 'product', 'name' => __('messages.filters.product')],
            ['id' => 'order', 'name' => __('messages.filters.order')],
        ];
    }
}
