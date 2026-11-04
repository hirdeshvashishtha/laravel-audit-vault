<?php

namespace HirdeshVashishtha\AuditVault\Traits;

use HirdeshVashishtha\AuditVault\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditVault
{
    public static function bootHasAuditVault(): void
    {
        if (! config('audit-vault.enabled', true)) {
            return;
        }

        if (App::runningInConsole() && ! config('audit-vault.log_console', false)) {
            return;
        }

        static::created(function (Model $model) {
            $model->recordAuditEvent('created', [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            unset($changes[$model->getUpdatedAtColumn()]);

            if (empty($changes)) {
                return;
            }

            $old = [];
            foreach ($changes as $key => $newValue) {
                $old[$key] = $model->getOriginal($key);
            }

            $model->recordAuditEvent('updated', $old, $changes);
        });

        static::deleted(function (Model $model) {
            $model->recordAuditEvent('deleted', $model->getOriginal(), []);
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    protected function recordAuditEvent(string $event, array $oldValues, array $newValues): void
    {
        $redacted = array_merge(
            config('audit-vault.redacted_fields', []),
            property_exists($this, 'auditRedacted') ? $this->auditRedacted : []
        );

        foreach ($redacted as $field) {
            if (array_key_exists($field, $oldValues)) {
                $oldValues[$field] = '[REDACTED]';
            }
            if (array_key_exists($field, $newValues)) {
                $newValues[$field] = '[REDACTED]';
            }
        }

        $userId = Auth::id();
        $ipAddress = Request::ip();

        $payload = json_encode([
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => (string) $this->getKey(),
            'user_id' => $userId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $ipAddress,
        ]);

        $signatureHash = hash_hmac('sha256', $payload, (string) config('app.key'));

        AuditLog::create([
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'user_id' => $userId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $ipAddress,
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'signature_hash' => $signatureHash,
            'created_at' => now(),
        ]);
    }
}
