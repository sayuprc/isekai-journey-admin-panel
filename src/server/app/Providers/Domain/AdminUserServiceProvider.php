<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use AdminUser\Application\Interactors\CreateInteractor;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Infrastructures\AdminUserFactory;
use AdminUser\Infrastructures\Hasher;

class AdminUserServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, FileAdminUserRepository::class);
        $this->app->bind(AdminUserFactoryInterface::class, AdminUserFactory::class);
        $this->app->bind(HasherInterface::class, Hasher::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
    }
}
