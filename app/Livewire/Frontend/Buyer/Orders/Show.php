<?php

namespace App\Livewire\Frontend\Buyer\Orders;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\Messaging\StartConversationAction;
use App\Enums\OrderStatus;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Order;
use App\Models\Users\Seller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;

    public Order $order;

    public int $currentOrderStep = 1;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);

        $this->order = $order->load(['items.product.primaryImage', 'items.seller', 'orderBundles.items.product.primaryImage']);
        $this->currentOrderStep = $this->order->lifecycleCurrentStep();
    }

    public function cancelOrder(): void
    {
        $this->updateStatus(OrderStatus::Cancelled->value);
    }

    public function updateStatus(string $status): void
    {
        $nextStatus = OrderStatus::tryFrom($status);

        if (! $nextStatus) {
            $this->notifyError(__('orders.status.messages.cannot_be_changed'));

            return;
        }

        try {
            $this->authorize('changeStatus', [$this->order, $nextStatus]);

            app(ChangeOrderStatusAction::class)->handle(
                order: $this->order,
                nextStatus: $nextStatus,
                actor: Auth::guard('buyer')->user(),
            );
        } catch (ValidationException $exception) {
            $this->notifyError(collect($exception->errors())->flatten()->first() ?? __('orders.status.messages.cannot_be_changed'));

            return;
        } catch (AuthorizationException $exception) {
            $this->notifyError($exception->getMessage() ?: __('orders.messages.unauthorized_update'));

            return;
        }

        $this->order->refresh()->load(['items.product.primaryImage', 'items.seller', 'orderBundles.items.product.primaryImage']);
        $this->currentOrderStep = $this->order->lifecycleCurrentStep();

        $this->notifySuccess(
            $nextStatus === OrderStatus::Cancelled
                ? __('orders.messages.cancelled_successfully')
                : __('orders.status.messages.updated')
        );
    }

    public function confirmCancelOrder(): void
    {
        $this->confirmAction(
            title: __('orders_confirm_cancel'),
            description: __('orders_confirm_cancel'),
            acceptLabel: __('orders_cancel_order'),
            method: 'cancelOrder',
            icon: 'warning',
        );
    }

    public function openSellerConversation(int $sellerId, StartConversationAction $action): void
    {
        $buyer = Auth::guard('buyer')->user();
        abort_if(! $buyer, 403);

        $seller = Seller::query()
            ->select(['id', 'name', 'company_name'])
            ->findOrFail($sellerId);

        try {
            $conversation = $action->forOrder($buyer, $this->order, $seller);
        } catch (AuthorizationException $exception) {
            $this->notifyError($exception->getMessage() ?: __('messages.errors.not_allowed'));

            return;
        }

        $this->redirectRoute('buyer.messages.show', $conversation, navigate: true);
    }

    public function render()
    {
        return view('frontend.buyer.orders.show', [
            'order' => $this->order,
            'orderSellers' => $this->order->items->pluck('seller')->filter()->unique('id')->values(),
            'orderStepItems' => $this->order->lifecycleSteps(),
            'orderStepPanel' => $this->order->lifecyclePanel(),
            'orderStepsColor' => $this->order->lifecycleStepsColor(),
            'orderTimelineItems' => $this->order->lifecycleTimeline(),
            'allowedStatusTransitions' => $this->order->availableTransitionsFor(Auth::guard('buyer')->user()),
        ]);
    }
}
