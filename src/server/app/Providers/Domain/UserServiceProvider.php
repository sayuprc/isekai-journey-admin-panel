<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\Facades\Auth;
use User\Application\Interactors\AuthenticateInteractor;
use User\Application\Interactors\CreateInteractor;
use User\Application\Interactors\LoginInteractor;
use User\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use User\Application\UseCase\Create\CreateUseCaseInterface;
use User\Application\UseCase\Login\LoginUseCaseInterface;
use User\DebugInfrastructures\FileCredentialRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Credential\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\CredentialFactoryInterface;
use User\Domain\Models\Credential\CredentialRepositoryInterface;
use User\Domain\Models\Credential\RefreshTokenFactoryInterface;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\HasherInterface;
use User\Domain\Services\Jwt\JwtConfigInterface;
use User\Domain\Services\Jwt\JwtHandlerInterface;
use User\Domain\Services\RandomTokenGeneratorInterface;
use User\Infrastructures\Auth\AuthUserProvider;
use User\Infrastructures\Credential\AccessTokenFactory;
use User\Infrastructures\Credential\CredentialFactory;
use User\Infrastructures\Credential\Jwt\JwtConfig;
use User\Infrastructures\Credential\Jwt\JwtHandler;
use User\Infrastructures\Credential\RefreshTokenFactory;
use User\Infrastructures\Hasher;
use User\Infrastructures\RandomTokenGenerator;
use User\Infrastructures\UserFactory;

class UserServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, FileUserRepository::class);
        $this->app->bind(UserFactoryInterface::class, UserFactory::class);
        $this->app->bind(HasherInterface::class, Hasher::class);
        $this->app->bind(JwtHandlerInterface::class, JwtHandler::class);
        $this->app->bind(JwtConfigInterface::class, JwtConfig::class);
        $this->app->bind(AccessTokenFactoryInterface::class, AccessTokenFactory::class);
        $this->app->bind(RefreshTokenFactoryInterface::class, RefreshTokenFactory::class);
        $this->app->bind(RandomTokenGeneratorInterface::class, RandomTokenGenerator::class);
        $this->app->bind(CredentialFactoryInterface::class, CredentialFactory::class);
        $this->app->bind(CredentialRepositoryInterface::class, FileCredentialRepository::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);
        $this->app->bind(AuthenticateUseCaseInterface::class, AuthenticateInteractor::class);
    }

    public function boot(): void
    {
        Auth::provider('custom', fn () => $this->app->make(AuthUserProvider::class));
    }
}
