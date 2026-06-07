<?php

namespace App\Livewire\Frontend\Buyer\Messages;

use App\Actions\Messaging\ArchiveConversationAction;
use App\Actions\Messaging\MarkConversationAsReadAction;
use App\Actions\Messaging\SendMessageAction;
use App\Models\Conversation;
use App\Models\Users\Buyer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Conversation $conversation;

    public string $body = '';

    public function mount(Conversation $conversation): void
    {
        $this->authorize('view', $conversation);

        $this->conversation = $conversation->load([
            'seller:id,name,company_name',
            'product:id,name',
            'order:id',
        ]);

        app(MarkConversationAsReadAction::class)->handle($this->conversation, $this->buyer());
    }

    public function sendMessage(SendMessageAction $action): void
    {
        try {
            $action->handle($this->conversation->fresh() ?? $this->conversation, $this->buyer(), $this->body);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, (string) collect($messages)->first());
            }

            return;
        } catch (AuthorizationException $exception) {
            session()->flash('message', $exception->getMessage() ?: __('messages.errors.not_allowed'));

            return;
        }

        $this->body = '';
        $this->conversation = $this->conversation->refresh()->load(['seller:id,name,company_name', 'product:id,name', 'order:id']);
        session()->flash('success', __('messages.sent_successfully'));
    }

    public function archive(ArchiveConversationAction $action): void
    {
        $action->handle($this->conversation, $this->buyer());

        session()->flash('success', __('messages.archived_successfully'));
        $this->redirectRoute('buyer.messages.index', navigate: true);
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

        return view('livewire.frontend.buyer.messages.show', [
            'actor' => $this->buyer(),
            'messages' => $messages,
        ]);
    }

    private function buyer(): Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        abort_if(! $buyer instanceof Buyer, 403);

        return $buyer;
    }
}
