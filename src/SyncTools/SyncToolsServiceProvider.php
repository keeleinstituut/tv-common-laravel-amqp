<?php

namespace SyncTools;

use Illuminate\Support\ServiceProvider;

class SyncToolsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/amqp.php' => config_path('amqp.php'),
            __DIR__.'/../config/pgsql-connection.php' => config_path('pgsql-connection.php'),
        ]);
    }

    public function register(): void
    {
        $this->app->singleton(AmqpConnectionRegistry::class, function () {
            return new AmqpConnectionRegistry;
        });

        $this->app->singleton(AmqpPublisher::class, function () {
            return new AmqpPublisher(app()->make(AmqpConnectionRegistry::class));
        });

        $this->app->singleton(AmqpConsumer::class, function () {
            return new AmqpConsumer(app()->make(AmqpConnectionRegistry::class));
        });

        $this->registerCommands();
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ConsumeCommand::class,
                Console\AmqpSetupCommand::class,
                Console\DbSchemasSetupCommand::class,
            ]);
        }
    }
}
