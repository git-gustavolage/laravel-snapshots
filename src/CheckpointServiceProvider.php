<?php

namespace Lageg\Checkpoint;

use Illuminate\Support\ServiceProvider;
use Lageg\Checkpoint\Storages\DatabaseStorage;

class CheckpointServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/checkpoint.php',
            'checkpoint'
        );

        $this->app->singleton('checkpoint.storage', DatabaseStorage::class);

        $this->app->bind('checkpoint.manager', function ($app) {
            $storage = $app->make('checkpoint.storage');

            return new CheckpointManager($storage);
        });

    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/winder.php' => config_path('winder.php'),
            ], 'winder-config');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations/2026_26_03_create_snapshots_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_snapshots_table.php'),
            ], 'winder-migrations');
        }
    }
}
