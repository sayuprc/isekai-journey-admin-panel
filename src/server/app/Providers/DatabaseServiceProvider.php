<?php

declare(strict_types=1);

namespace App\Providers;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Support\Infrastructures\Database\SQLiteConnector;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GrammarInterface::class, DefaultGrammar::class);

        $this->app->singleton(SQLiteConnector::class);

        $this->app->bind(
            PDOInterface::class,
            fn (Container $container): PDOInterface => $container->make(SQLiteConnector::class)->connect(),
        );
    }

    public function boot(): void
    {
    }
}
