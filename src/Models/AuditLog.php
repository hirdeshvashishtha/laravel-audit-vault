<?php

namespace HirdeshVashishtha\AuditVault\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('audit-vault.table', 'audit_vault_logs'));

        if ($connection = config('audit-vault.connection')) {
            $this->setConnection($connection);
        }
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit logs are immutable and cannot be modified.'));
        static::deleting(fn () => throw new LogicException('Audit logs are immutable and cannot be deleted.'));
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function verifyIntegrity(): bool
    {
        $payload = json_encode([
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => (string) $this->auditable_id,
            'user_id' => $this->user_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'ip_address' => $this->ip_address,
        ]);

        $expectedHash = hash_hmac('sha256', $payload, (string) config('app.key'));

        return hash_equals($expectedHash, $this->signature_hash);
    }
}
