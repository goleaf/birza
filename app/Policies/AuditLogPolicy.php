<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\Users\Admin;
use Illuminate\Contracts\Auth\Authenticatable;

class AuditLogPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin;
    }

    public function view(Authenticatable $user, AuditLog $auditLog): bool
    {
        return $user instanceof Admin;
    }

    public function create(Authenticatable $user): bool
    {
        return false;
    }

    public function update(Authenticatable $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function restore(Authenticatable $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
