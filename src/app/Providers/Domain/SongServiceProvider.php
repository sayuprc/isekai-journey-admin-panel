<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Song\Application\List\ListInteractor;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Repositories\FileSongRepository;
use Song\UseCases\List\ListUseCaseInterface;

class SongServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
    }
}
