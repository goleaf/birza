<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogService
{
    /**
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'access_token',
        'api_token',
        'secret',
        'secret_key',
        'client_secret',
        'authorization',
        'auth_code',
        'verification_code',
        'otp',
        'card_number',
        'payment_card',
        'cvv',
        'cvc',
        'private_key',
        'bank_account',
        'raw_file',
        'file_contents',
    ];

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        ?Authenticatable $actor,
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?string $reason = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= app()->bound('request') ? request() : null;

        return AuditLog::query()->create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_type' => $actor ? $actor::class : null,
            'actor_role' => $this->actorRole($actor),
            'action' => $action,
            'auditable_id' => $auditable?->getKey(),
            'auditable_type' => $auditable ? $auditable::class : null,
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'metadata' => $this->sanitize($metadata),
            'reason' => $this->cleanText($reason),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function logForCurrentAdmin(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?string $reason = null,
    ): AuditLog {
        return $this->log(
            actor: Auth::guard('admin')->user(),
            action: $action,
            auditable: $auditable,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: $metadata,
            reason: $reason,
        );
    }

    /**
     * @param  list<string>|null  $only
     * @return array<string, mixed>
     */
    public function snapshot(Model $model, ?array $only = null): array
    {
        $attributes = $model->getAttributes();

        if ($only !== null) {
            $attributes = collect($attributes)->only($only)->all();
        }

        return $this->sanitize($attributes) ?? [];
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    public function changedValues(array $oldValues, array $newValues): array
    {
        $keys = collect(array_keys($oldValues))
            ->merge(array_keys($newValues))
            ->unique()
            ->values();

        $old = [];
        $new = [];

        foreach ($keys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($oldValue === $newValue) {
                continue;
            }

            $old[$key] = $oldValue;
            $new[$key] = $newValue;
        }

        return ['old' => $old, 'new' => $new];
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    public function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return $this->sanitizeArray($values);
    }

    public function actorRole(?Authenticatable $actor): string
    {
        return match (true) {
            $actor instanceof Admin => 'admin',
            $actor instanceof Buyer => 'buyer',
            $actor instanceof Seller => 'seller',
            $actor === null => 'system',
            default => Str::of(class_basename($actor))->snake()->toString(),
        };
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitizeArray(array $values, int $depth = 0): array
    {
        if ($depth > 6) {
            return ['truncated' => true];
        }

        $sanitized = [];

        foreach ($values as $key => $value) {
            $key = is_string($key) ? $key : (string) $key;

            if ($this->isSensitiveKey($key)) {
                continue;
            }

            $sanitized[$key] = $this->sanitizeValue($value, $depth + 1);
        }

        return $sanitized;
    }

    private function sanitizeValue(mixed $value, int $depth): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'original_name' => $value->getClientOriginalName(),
                'mime_type' => $value->getMimeType(),
                'size' => $value->getSize(),
            ];
        }

        if ($value instanceof Model) {
            return [
                'type' => $value::class,
                'id' => $value->getKey(),
            ];
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            return $this->sanitizeArray($value, $depth);
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return $value;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', ' '], '_')->toString();

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if ($normalized === $sensitiveKey || str_contains($normalized, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
