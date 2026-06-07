<?php

namespace App\Models;

use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Database\Factories\ProductReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReport extends Model
{
    /** @use HasFactory<ProductReportFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'reporter_id',
        'reporter_email',
        'reporter_fingerprint',
        'reason',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'reporter_id' => 'integer',
            'reason' => ProductReportReason::class,
            'status' => ProductReportStatus::class,
            'reviewed_by' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'reporter_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function reasonLabel(): string
    {
        return $this->reason->label();
    }

    public function statusBadgeClass(): string
    {
        return $this->status->badgeClass();
    }

    public function reporterLabel(): string
    {
        if ($this->reporter) {
            return $this->reporter->name.' ('.$this->reporter->email.')';
        }

        return $this->reporter_email ?: __('reports.product.guest_reporter');
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }
}
