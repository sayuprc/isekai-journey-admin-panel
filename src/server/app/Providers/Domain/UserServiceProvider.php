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
use User\DebugInfrastructures\FileRefreshTokenRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use User\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use User\Domain\Services\HasherInterface;
use User\Infrastructures\Auth\AuthUserProvider;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use User\Infrastructures\Credential\AccessToken\JwtConfig;
use User\Infrastructures\Credential\AccessToken\JwtHandler;
use User\Infrastructures\Credential\RefreshToken\RandomTokenGenerator;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
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
        $this->app->bind(JwtConfigInterface::class, JwtConfig::class);
        $this->app->bind(AccessTokenFactoryInterface::class, AccessTokenFactory::class);
        $this->app->bind(RefreshTokenFactoryInterface::class, RefreshTokenFactory::class);
        $this->app->bind(RandomTokenGeneratorInterface::class, RandomTokenGenerator::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, FileRefreshTokenRepository::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);
        $this->app->bind(AuthenticateUseCaseInterface::class, AuthenticateInteractor::class);
    }

    public function boot(): void
    {
        Auth::provider('custom', fn () => $this->app->make(AuthUserProvider::class));
    }
}
