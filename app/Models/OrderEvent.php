<?php

namespace App\Models;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use Database\Factories\OrderEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    /** @use HasFactory<OrderEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'actor_id',
        'actor_role',
        'event_type',
        'old_status',
        'new_status',
        'title_key',
        'description_key',
        'public_note',
        'internal_note',
        'metadata',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actor_id' => 'integer',
            'actor_role' => OrderStatusActorRole::class,
            'event_type' => OrderEventType::class,
            'old_status' => OrderStatus::class,
            'new_status' => OrderStatus::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeForOrder(Builder $query, Order|int $order): Builder
    {
        $orderId = $order instanceof Order ? $order->id : $order;

        return $query->where('order_id', $orderId);
    }

    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->whereNotNull('title_key');
    }

    public function scopeInternalOnly(Builder $query): Builder
    {
        return $query
            ->whereNotNull('internal_note')
            ->whereNull('public_note');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->oldest('created_at');
    }

    public function scopeOfType(Builder $query, OrderEventType|string $type): Builder
    {
        $value = $type instanceof OrderEventType ? $type->value : $type;

        return $query->where('event_type', $value);
    }

    public function actorLabel(): ?string
    {
        return $this->actor_role?->label();
    }
}
