<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Invitation\InvitationRepositoryInterface;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Domain\Services\PlainTokenGeneratorInterface;
use AdminUser\Domain\Services\TokenHasherInterface;
use AdminUser\Infrastructures\AdminUserRepository;
use AdminUser\Infrastructures\Hasher;
use AdminUser\Infrastructures\InvitationRepository;
use AdminUser\Infrastructures\PlainTokenGenerator;
use AdminUser\Infrastructures\TokenHasher;
use Illuminate\Support\ServiceProvider;
use Override;

class AdminUserServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->bind(HasherInterface::class, Hasher::class);
        $this->app->bind(InvitationRepositoryInterface::class, InvitationRepository::class);
        $this->app->bind(PlainTokenGeneratorInterface::class, PlainTokenGenerator::class);
        $this->app->bind(
            TokenHasherInterface::class,
            fn (): TokenHasher => new TokenHasher(config()->string('app.key')),
        );
    }
}
