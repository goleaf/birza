<?php

namespace App\Models;

use App\Enums\ProductQuestionStatus;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Database\Factories\ProductQuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class ProductQuestion extends Model
{
    /** @use HasFactory<ProductQuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'seller_id',
        'buyer_id',
        'answered_by_seller_id',
        'moderated_by_admin_id',
        'question',
        'answer',
        'answered_at',
        'status',
        'is_public',
        'guest_name',
        'guest_email',
        'moderated_at',
        'moderation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'seller_id' => 'integer',
            'buyer_id' => 'integer',
            'answered_by_seller_id' => 'integer',
            'moderated_by_admin_id' => 'integer',
            'answered_at' => 'datetime',
            'moderated_at' => 'datetime',
            'status' => ProductQuestionStatus::class,
            'is_public' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function answeredBySeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'answered_by_seller_id');
    }

    public function moderatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'moderated_by_admin_id');
    }

    public function scopePublicAnswered(Builder $query): Builder
    {
        return $query
            ->where('status', ProductQuestionStatus::Answered->value)
            ->where('is_public', true)
            ->whereNotNull('answer')
            ->whereNotNull('answered_at');
    }

    public function scopeForSeller(Builder $query, Seller|int $seller): Builder
    {
        $sellerId = $seller instanceof Seller ? $seller->getKey() : $seller;

        return $query->where('seller_id', $sellerId);
    }

    public function scopeUnanswered(Builder $query): Builder
    {
        return $query
            ->where('status', ProductQuestionStatus::Pending->value)
            ->whereNull('answer');
    }

    public function scopeVisibleToAdmin(Builder $query): Builder
    {
        return $query->withTrashed();
    }

    public function authorLabel(): string
    {
        return $this->buyer?->company_name
            ?: $this->buyer?->name
            ?: $this->guest_name
            ?: __('products.questions.guest_author');
    }

    public function statusLabel(): string
    {
        return ProductQuestionStatus::fromValue($this->status)->label();
    }

    public function statusBadgeColor(): string
    {
        return ProductQuestionStatus::fromValue($this->status)->uiBadgeColor();
    }

    public function statusMaryBadgeClass(): string
    {
        return ProductQuestionStatus::fromValue($this->status)->maryBadgeClass();
    }

    public function isAnswerable(): bool
    {
        return ! $this->trashed()
            && in_array(ProductQuestionStatus::fromValue($this->status), [
                ProductQuestionStatus::Pending,
                ProductQuestionStatus::Answered,
            ], true);
    }

    public function markAnswered(Seller $seller, string $answer, ?Carbon $answeredAt = null): void
    {
        $this->forceFill([
            'answer' => trim($answer),
            'answered_by_seller_id' => $seller->getKey(),
            'answered_at' => $answeredAt ?? now(),
            'status' => ProductQuestionStatus::Answered,
            'is_public' => true,
        ])->save();
    }
}
