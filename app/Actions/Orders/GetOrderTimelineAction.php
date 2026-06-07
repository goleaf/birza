<?php

namespace App\Actions\Orders;

use App\Enums\OrderEventType;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderStatusHistory;
use App\Support\LocaleFormatter;
use Illuminate\Support\Collection;

class GetOrderTimelineAction
{
    /**
     * @return Collection<int, array{
     *     title: string,
     *     subtitle: ?string,
     *     description: string,
     *     icon: string,
     *     pending: bool,
     *     tone: string,
     *     public_note?: ?string,
     *     internal_note?: ?string,
     *     actor_label?: ?string
     * }>
     */
    public function handle(Order $order, bool $includeInternal = false): Collection
    {
        $events = $this->events($order);

        if ($events->isNotEmpty()) {
            return $events->map(fn (OrderEvent $event): array => $this->eventItem($event, $includeInternal))->values();
        }

        $statusHistory = $this->statusHistory($order);

        if ($statusHistory->isNotEmpty()) {
            return $statusHistory
                ->map(fn (OrderStatusHistory $history): array => $this->historyItem($history, $includeInternal))
                ->values();
        }

        return collect($order->lifecycleTimeline());
    }

    /**
     * @return Collection<int, OrderEvent>
     */
    private function events(Order $order): Collection
    {
        if ($order->relationLoaded('events')) {
            return $order->events->sortBy('created_at')->values();
        }

        return $order->events()
            ->publicVisible()
            ->oldestFirst()
            ->get();
    }

    /**
     * @return Collection<int, OrderStatusHistory>
     */
    private function statusHistory(Order $order): Collection
    {
        if ($order->relationLoaded('statusHistory')) {
            return $order->statusHistory->sortBy('created_at')->values();
        }

        return $order->statusHistory()
            ->oldest('created_at')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function eventItem(OrderEvent $event, bool $includeInternal): array
    {
        $eventType = $event->event_type;

        $item = [
            'title' => $event->title_key ?? $eventType->titleKey(),
            'subtitle' => LocaleFormatter::dateTime($event->created_at),
            'description' => $event->description_key ?? $eventType->descriptionKey(),
            'icon' => $eventType->icon(),
            'pending' => false,
            'tone' => $eventType->tone(),
            'public_note' => $event->public_note,
            'actor_label' => $event->actorLabel(),
        ];

        if ($includeInternal) {
            $item['internal_note'] = $event->internal_note;
        }

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    private function historyItem(OrderStatusHistory $history, bool $includeInternal): array
    {
        $eventType = OrderEventType::fromOrderStatus($history->new_status);

        $item = [
            'title' => $eventType->titleKey(),
            'subtitle' => LocaleFormatter::dateTime($history->created_at),
            'description' => $eventType->descriptionKey(),
            'icon' => $eventType->icon(),
            'pending' => false,
            'tone' => $eventType->tone(),
            'public_note' => $history->note,
            'actor_label' => $history->changed_by_role->label(),
        ];

        if ($includeInternal) {
            $item['internal_note'] = $history->reason;
        }

        return $item;
    }
}
