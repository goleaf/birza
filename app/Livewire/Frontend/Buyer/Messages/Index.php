<?php

namespace App\Livewire\Frontend\Buyer\Messages;

use App\Models\Conversation;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(except: 'all')]
    public string $filter = 'all';

    public function mount(): void
    {
        $this->authorize('viewAny', Conversation::class);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $buyer = $this->buyer();

        $conversations = Conversation::query()
            ->summaryColumns()
            ->forBuyer($buyer)
            ->with([
                'seller:id,name,company_name',
                'product:id,name',
                'order:id',
                'latestMessage:id,conversation_id,sender_id,sender_role,body,read_at,created_at',
                'latestMessage.senderBuyer:id,name,company_name',
                'latestMessage.senderSeller:id,name,company_name',
                'latestMessage.senderAdmin:id,name',
            ])
            ->withUnreadCountFor($buyer)
            ->when($this->filter !== 'archived', fn ($query) => $query->whereNull('buyer_archived_at'))
            ->when($this->filter === 'archived', fn ($query) => $query->whereNotNull('buyer_archived_at'))
            ->when($this->filter === 'unread', fn ($query) => $query->unreadFor($buyer))
            ->when($this->filter === 'product', fn ($query) => $query->whereNotNull('product_id'))
            ->when($this->filter === 'order', fn ($query) => $query->whereNotNull('order_id'))
            ->latestActivity()
            ->paginate(10)
            ->withQueryString();

        return view('livewire.frontend.buyer.messages.index', [
            'conversations' => $conversations,
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    private function buyer(): Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        abort_if(! $buyer instanceof Buyer, 403);

        return $buyer;
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function filterOptions(): array
    {
        return [
            ['id' => 'all', 'name' => __('messages.filters.all')],
            ['id' => 'unread', 'name' => __('messages.filters.unread')],
            ['id' => 'product', 'name' => __('messages.filters.product')],
            ['id' => 'order', 'name' => __('messages.filters.order')],
            ['id' => 'archived', 'name' => __('messages.filters.archived')],
        ];
    }
}
