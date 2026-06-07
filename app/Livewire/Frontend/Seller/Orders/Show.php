<?php

namespace App\Livewire\Frontend\Seller\Orders;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\Messaging\StartConversationAction;
use App\Enums\OrderStatus;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Order;
use App\Models\OrderItem;
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

    public $orderItems;

    public $orderBundles;

    public ?string $comment = null;

    public int $currentOrderStep = 1;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);

        $orderItems = OrderItem::with(['order', 'product.primaryImage'])
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('order_id', $order->id)
            ->get();

        if ($orderItems->isEmpty()) {
            abort(403, __('orders.messages.unauthorized_view'));
        }

        $this->order = $order->load('buyer');
        $this->orderItems = $orderItems;
        $this->orderBundles = $order->orderBundles()
            ->with('items.product.primaryImage')
            ->where('seller_id', Auth::guard('seller')->id())
            ->get();
        $this->currentOrderStep = $this->order->lifecycleCurrentStep();
    }

    public function updateStatus(string $status): void
    {
        $nextStatus = OrderStatus::tryFrom($status);
        $seller = Auth::guard('seller')->user();

        if (! $seller) {
            abort(403);
        }

        if (! $nextStatus) {
            $this->notifyError(__('common_error_occurred'));

            return;
        }

        $this->validate([
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $comment = trim((string) ($this->comment ?? ''));

        try {
            $this->authorize('changeStatus', [$this->order, $nextStatus]);

            app(ChangeOrderStatusAction::class)->handle(
                order: $this->order,
                nextStatus: $nextStatus,
                actor: $seller,
                note: $comment,
            );
        } catch (ValidationException $exception) {
            $this->notifyError(collect($exception->errors())->flatten()->first() ?? __('orders.status.messages.cannot_be_changed'));

            return;
        } catch (AuthorizationException $exception) {
            $this->notifyError($exception->getMessage() ?: __('orders.messages.unauthorized_update'));

            return;
        }

        $this->order->refresh()->load('buyer');
        $this->orderBundles = $this->order->orderBundles()
            ->with('items.product.primaryImage')
            ->where('seller_id', Auth::guard('seller')->id())
            ->get();
        $this->comment = null;
        $this->currentOrderStep = $this->order->lifecycleCurrentStep();

        $this->notifySuccess(__('orders.status.messages.updated'));
    }

    public function confirmCancelOrder(): void
    {
        $this->confirmAction(
            title: __('orders_confirm_cancel'),
            description: __('orders_confirm_cancel'),
            acceptLabel: __('orders_cancel_order'),
            method: 'updateStatus',
            params: OrderStatus::Cancelled->value,
            icon: 'warning',
        );
    }

    public function openBuyerConversation(StartConversationAction $action): void
    {
        $seller = Auth::guard('seller')->user();

        if (! $seller) {
            abort(403);
        }

        try {
            $conversation = $action->forOrder($seller, $this->order, $seller);
        } catch (AuthorizationException $exception) {
            $this->notifyError($exception->getMessage() ?: __('messages.errors.not_allowed'));

            return;
        }

        $this->redirectRoute('seller.messages.show', $conversation, navigate: true);
    }

    public function render()
    {
        return view('frontend.seller.orders.show', [
            'order' => $this->order,
            'orderItems' => $this->orderItems,
            'orderBundles' => $this->orderBundles,
            'orderStepItems' => $this->order->lifecycleSteps(),
            'orderStepPanel' => $this->order->lifecyclePanel(),
            'orderStepsColor' => $this->order->lifecycleStepsColor(),
            'orderTimelineItems' => $this->order->lifecycleTimeline(),
            'orderStatuses' => OrderStatus::cases(),
            'allowedStatusTransitions' => $this->order->availableTransitionsFor(Auth::guard('seller')->user()),
            'acceptedStatus' => OrderStatus::Accepted,
        ]);
    }
}
