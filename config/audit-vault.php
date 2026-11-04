<?php

return [
    'enabled' => env('AUDIT_VAULT_ENABLED', true),
    'connection' => env('AUDIT_VAULT_DB_CONNECTION', null),
    'table' => env('AUDIT_VAULT_TABLE', 'audit_vault_logs'),
    'redacted_fields' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'api_token',
        'secret',
        'cvv',
    ],
    'log_console' => env('AUDIT_VAULT_LOG_CONSOLE', false),
];
