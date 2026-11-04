# Laravel Audit Vault

Enterprise-grade immutable Eloquent audit logging with cryptographic SHA-256 tamper detection and JSON diff tracking for Laravel.

## Features

- **Immutable Audit Logging**: Store comprehensive audit logs for all Eloquent model mutations.
- **Cryptographic SHA-256 Tamper Detection**: Each log entry is hashed and chained to prevent unauthorized modifications.
- **JSON Diff Tracking**: Fine-grained change tracking with structured JSON diffs.
- **Zero-Config Eloquent Trait**: Simple integration into existing Laravel models via the `Auditable` trait.

## Installation

```bash
composer require hirdeshvashishtha/laravel-audit-vault
```

## Quick Start

Add the `Auditable` trait to your Eloquent model:

```php
use HirdeshVashishtha\AuditVault\Traits\Auditable;

class Order extends Model
{
    use Auditable;
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
