<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lageg\Checkpoint\CheckpointServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            CheckpointServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('checkpoint.default_storage', 'database');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app['db']->connection()->getSchemaBuilder()->create('fake_models', function ($table) {
            $table->id();
            $table->string('value');
            $table->string('other_value')->default('other value');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('another_fake_models', function ($table) {
            $table->id();
            $table->string('value');
            $table->string('other_value')->default('other value');
            $table->timestamps();
        });
    }
}
