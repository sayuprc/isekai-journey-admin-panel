<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use SongType\Application\Interactors\ListInteractor;
use SongType\Application\UseCase\List\ListUseCaseInterface;

class SongTypeServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
    }
}
