<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use User\Application\Interactors\CreateInteractor;
use User\Application\UseCase\Create\CreateUseCaseInterface;
use User\DebugInfrastructures\FileCredentialRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Credential\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\CredentialFactoryInterface;
use User\Domain\Models\Credential\CredentialRepositoryInterface;
use User\Domain\Models\Credential\RefreshTokenFactoryInterface;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\HasherInterface;
use User\Domain\Services\Jwt\JwtHandlerInterface;
use User\Infrastructures\Credential\AccessTokenFactory;
use User\Infrastructures\Credential\CredentialFactory;
use User\Infrastructures\Credential\Jwt\JwtHandler;
use User\Infrastructures\Credential\RefreshTokenFactory;
use User\Infrastructures\Hasher;
use User\Infrastructures\UserFactory;

class UserServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, FileUserRepository::class);
        $this->app->bind(UserFactoryInterface::class, UserFactory::class);
        $this->app->bind(HasherInterface::class, Hasher::class);
        $this->app->bind(JwtHandlerInterface::class, JwtHandler::class);
        $this->app->bind(AccessTokenFactoryInterface::class, AccessTokenFactory::class);
        $this->app->bind(RefreshTokenFactoryInterface::class, RefreshTokenFactory::class);
        $this->app->bind(CredentialFactoryInterface::class, CredentialFactory::class);
        $this->app->bind(CredentialRepositoryInterface::class, FileCredentialRepository::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
    }
}
