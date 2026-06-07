<?php

namespace App\Actions\StockAlerts\Concerns;

use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait LogsStockAlertAudit
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    private function logStockAlertAudit(
        ?Authenticatable $actor,
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
    ): void {
        if (! class_exists(AuditLogService::class) || ! Schema::hasTable('audit_logs')) {
            return;
        }

        app(AuditLogService::class)->log(
            actor: $actor,
            action: $action,
            auditable: $auditable,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: $metadata,
        );
    }
}
