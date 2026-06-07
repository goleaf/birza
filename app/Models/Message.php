<?php

namespace App\Models;

use App\Enums\MessageSenderRole;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Database\Factories\MessageFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'body',
        'read_at',
        'edited_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
            'sender_id' => 'integer',
            'sender_role' => MessageSenderRole::class,
            'metadata' => 'array',
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderBuyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'sender_id')->withTrashed();
    }

    public function senderSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'sender_id')->withTrashed();
    }

    public function senderAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeSummaryColumns(Builder $query): Builder
    {
        return $query->select([
            'id',
            'conversation_id',
            'sender_id',
            'sender_role',
            'body',
            'read_at',
            'edited_at',
            'metadata',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
    }

    public function isFrom(Authenticatable $actor): bool
    {
        $role = MessageSenderRole::fromActor($actor);

        return $role === $this->sender_role
            && (int) $this->sender_id === (int) $actor->getAuthIdentifier();
    }

    public function senderLabel(): string
    {
        return match ($this->sender_role) {
            MessageSenderRole::Buyer => $this->senderBuyer?->company_name ?: $this->senderBuyer?->name ?: __('messages.sender_roles.buyer'),
            MessageSenderRole::Seller => $this->senderSeller?->company_name ?: $this->senderSeller?->name ?: __('messages.sender_roles.seller'),
            MessageSenderRole::Admin => $this->senderAdmin?->name ?: __('messages.sender_roles.admin'),
            default => __('common_not_specified'),
        };
    }

    public function preview(int $limit = 120): string
    {
        return str((string) $this->body)
            ->squish()
            ->limit($limit)
            ->toString();
    }
}
