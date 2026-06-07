<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_type',
        'actor_role',
        'action',
        'auditable_id',
        'auditable_type',
        'old_values',
        'new_values',
        'metadata',
        'reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actor_id' => 'integer',
            'auditable_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeAction(Builder $query, string|array $action): Builder
    {
        $this->selectAuditColumns($query);

        return is_array($action)
            ? $query->whereIn('action', $action)
            : $query->where('action', $action);
    }

    public function scopeActor(Builder $query, Authenticatable|Model $actor): Builder
    {
        $this->selectAuditColumns($query);

        return $query
            ->where('actor_type', $actor::class)
            ->where('actor_id', $actor instanceof Model ? $actor->getKey() : $actor->getAuthIdentifier());
    }

    public function scopeEntity(Builder $query, Model|string $entity, ?int $entityId = null): Builder
    {
        $this->selectAuditColumns($query);

        if ($entity instanceof Model) {
            return $query
                ->where('auditable_type', $entity::class)
                ->where('auditable_id', $entity->getKey());
        }

        $query->where('auditable_type', $entity);

        if ($entityId !== null) {
            $query->where('auditable_id', $entityId);
        }

        return $query;
    }

    public function scopeCreatedFrom(Builder $query, string|Carbon $date): Builder
    {
        $this->selectAuditColumns($query);

        return $query->where('created_at', '>=', $date instanceof Carbon ? $date : Carbon::parse($date)->startOfDay());
    }

    public function scopeCreatedUntil(Builder $query, string|Carbon $date): Builder
    {
        $this->selectAuditColumns($query);

        return $query->where('created_at', '<=', $date instanceof Carbon ? $date : Carbon::parse($date)->endOfDay());
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        $this->selectAuditColumns($query);

        return $query->where('actor_role', $role);
    }

    public function actorLabel(): string
    {
        $actor = $this->actor;
        $name = data_get($actor, 'name') ?: data_get($actor, 'email');

        return collect([$this->actor_role, $name, $this->actor_id ? '#'.$this->actor_id : null])
            ->filter()
            ->join(' ');
    }

    public function auditableLabel(): string
    {
        $name = class_basename((string) $this->auditable_type);

        return trim($name.' #'.$this->auditable_id);
    }

    /**
     * @return list<string>
     */
    private function auditColumns(): array
    {
        return [
            'id',
            'actor_id',
            'actor_type',
            'actor_role',
            'action',
            'auditable_id',
            'auditable_type',
            'old_values',
            'new_values',
            'metadata',
            'reason',
            'ip_address',
            'user_agent',
            'created_at',
        ];
    }

    private function selectAuditColumns(Builder $query): void
    {
        if ($query->getQuery()->columns !== null) {
            return;
        }

        $query->select($this->auditColumns());
    }
}
