<?php

namespace App\Models;

use App\Models\Users\Admin;
use Database\Factories\AdminActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAction extends Model
{
    /** @use HasFactory<AdminActionFactory> */
    use HasFactory;

    protected $fillable = [
        'actor_user_id',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'metadata',
        'reason',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actor_user_id' => 'integer',
            'entity_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'actor_user_id');
    }

    public function entityLabel(): string
    {
        if ($this->entity_type === null) {
            return __('admin_actions_entity_none');
        }

        return trim(class_basename($this->entity_type).' #'.$this->entity_id);
    }
}
