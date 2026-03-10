<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use AdminUser\Application\Interactors\CreateInteractor;
use AdminUser\Application\Interactors\ListInteractor;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use AdminUser\Application\UseCase\List\ListUseCaseInterface;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Infrastructures\AdminUserFactory;
use AdminUser\Infrastructures\AdminUserRepository;
use AdminUser\Infrastructures\Hasher;
use Override;

class AdminUserServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->bind(AdminUserFactoryInterface::class, AdminUserFactory::class);
        $this->app->bind(HasherInterface::class, Hasher::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
    }
}
