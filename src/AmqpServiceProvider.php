<?php

namespace Amqp;

use Illuminate\Support\ServiceProvider;

class AmqpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/amqp.php' => config_path('amqp.php'),
        ]);
    }

    public function register(): void
    {
        $this->app->singleton(ConnectionRegistry::class, function () {
            return new ConnectionRegistry;
        });

        $this->app->singleton(Publisher::class, function () {
            return new Publisher(app()->make(ConnectionRegistry::class));
        });

        $this->app->singleton(Consumer::class, function () {
            return new Consumer(app()->make(ConnectionRegistry::class));
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ConsumeCommand::class,
                Console\AmqpSetupCommand::class,
                Console\DbSchemasSetupCommand::class
            ]);

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
