<?php

namespace HirdeshVashishtha\AuditVault;

use Illuminate\Support\ServiceProvider;

class AuditVaultServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/audit-vault.php', 'audit-vault');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/audit-vault.php' => config_path('audit-vault.php'),
            ], 'audit-vault-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'audit-vault-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
