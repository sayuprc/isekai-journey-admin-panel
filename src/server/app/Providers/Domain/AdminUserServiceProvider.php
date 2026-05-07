<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Infrastructures\AdminUserRepository;
use AdminUser\Infrastructures\Hasher;
use Illuminate\Support\ServiceProvider;
use Override;

class AdminUserServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->bind(HasherInterface::class, Hasher::class);
    }
}
