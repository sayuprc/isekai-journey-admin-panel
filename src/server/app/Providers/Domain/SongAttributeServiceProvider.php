<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use SongAttribute\Application\Interactors\ListInteractor;
use SongAttribute\Application\UseCase\List\ListUseCaseInterface;

class SongAttributeServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
    }
}
