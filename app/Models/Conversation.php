<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\MessageSenderRole;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Database\Factories\ConversationFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'last_message_at',
        'buyer_archived_at',
        'seller_archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buyer_id' => 'integer',
            'seller_id' => 'integer',
            'product_id' => 'integer',
            'order_id' => 'integer',
            'status' => ConversationStatus::class,
            'last_message_at' => 'datetime',
            'buyer_archived_at' => 'datetime',
            'seller_archived_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class)->withTrashed();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class)->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function scopeSummaryColumns(Builder $query): Builder
    {
        return $query->select([
            'id',
            'buyer_id',
            'seller_id',
            'product_id',
            'order_id',
            'status',
            'last_message_at',
            'buyer_archived_at',
            'seller_archived_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeForBuyer(Builder $query, Buyer|int $buyer): Builder
    {
        $buyerId = $buyer instanceof Buyer ? $buyer->getKey() : $buyer;

        return $query->where('buyer_id', $buyerId);
    }

    public function scopeForSeller(Builder $query, Seller|int $seller): Builder
    {
        $sellerId = $seller instanceof Seller ? $seller->getKey() : $seller;

        return $query->where('seller_id', $sellerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ConversationStatus::Active->value);
    }

    public function scopeLatestActivity(Builder $query): Builder
    {
        return $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at');
    }

    public function scopeUnreadFor(Builder $query, Buyer|Seller $actor): Builder
    {
        $role = MessageSenderRole::fromActor($actor);

        return $query->whereHas('messages', function (Builder $messageQuery) use ($role): void {
            $messageQuery
                ->visible()
                ->where('sender_role', '!=', $role->value)
                ->whereNull('read_at');
        });
    }

    public function scopeWithUnreadCountFor(Builder $query, Buyer|Seller $actor): Builder
    {
        $role = MessageSenderRole::fromActor($actor);

        return $query->withCount([
            'messages as unread_messages_count' => function (Builder $messageQuery) use ($role): void {
                $messageQuery
                    ->visible()
                    ->where('sender_role', '!=', $role->value)
                    ->whereNull('read_at');
            },
        ]);
    }

    public function participantRole(Authenticatable $actor): ?MessageSenderRole
    {
        return match (true) {
            $actor instanceof Buyer && (int) $this->buyer_id === (int) $actor->getAuthIdentifier() => MessageSenderRole::Buyer,
            $actor instanceof Seller && (int) $this->seller_id === (int) $actor->getAuthIdentifier() => MessageSenderRole::Seller,
            default => null,
        };
    }

    public function isParticipant(Authenticatable $actor): bool
    {
        return $this->participantRole($actor) !== null;
    }

    public function isArchivedFor(Authenticatable $actor): bool
    {
        return match ($this->participantRole($actor)) {
            MessageSenderRole::Buyer => $this->buyer_archived_at !== null,
            MessageSenderRole::Seller => $this->seller_archived_at !== null,
            default => false,
        };
    }

    public function canReceiveMessages(): bool
    {
        return ($this->status ?? ConversationStatus::Active)->canReceiveMessages();
    }

    public function relatedLabel(): string
    {
        if ($this->order_id !== null) {
            return __('messages.related_order_number', ['order' => $this->order_id]);
        }

        if ($this->product_id !== null) {
            return $this->product?->name
                ? __('messages.related_product_name', ['product' => $this->product->name])
                : __('messages.related_product');
        }

        return __('messages.general_inquiry');
    }
}
