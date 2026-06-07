<?php

namespace App\Livewire\Backend\Messages;

use App\Actions\Messaging\RecordMessagingAuditAction;
use App\Models\Conversation;
use App\Models\Users\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Conversation $conversation;

    public function mount(Conversation $conversation): void
    {
        $this->authorize('moderate', $conversation);

        $this->conversation = $conversation->load([
            'buyer:id,name,company_name,email',
            'seller:id,name,company_name,email',
            'product:id,name',
            'order:id',
        ]);

        app(RecordMessagingAuditAction::class)
            ->adminViewed($this->admin(), $this->conversation, 'admin_messages_show');
    }

    public function render(): View
    {
        $messages = $this->conversation->messages()
            ->summaryColumns()
            ->visible()
            ->with([
                'senderBuyer:id,name,company_name',
                'senderSeller:id,name,company_name',
                'senderAdmin:id,name',
            ])
            ->latest('created_at')
            ->paginate(25, pageName: 'messagesPage')
            ->withQueryString();

        return view('livewire.backend.messages.show', [
            'messages' => $messages,
        ]);
    }

    private function admin(): Admin
    {
        $admin = Auth::guard('admin')->user();

        abort_if(! $admin instanceof Admin, 403);

        return $admin;
    }
}
