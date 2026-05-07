<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Auth\Infrastructures\Auth\AuthAdminUserRepository;
use Auth\Infrastructures\Auth\AuthUserProvider;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use Auth\Infrastructures\Token\AccessToken\AccessTokenFactory;
use Auth\Infrastructures\Token\AccessToken\JwtHandler;
use Auth\Infrastructures\Token\RefreshToken\RandomTokenGenerator;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Auth\Infrastructures\Token\RefreshToken\TokenHasher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Override;
use Support\UseCase\Authorizer\AuthorizationContextInterface;

class AuthServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(JwtHandlerInterface::class, JwtHandler::class);
        $this->app->bind(AccessTokenFactoryInterface::class, AccessTokenFactory::class);
        $this->app->bind(RandomTokenGeneratorInterface::class, RandomTokenGenerator::class);
        $this->app->bind(TokenHasherInterface::class, TokenHasher::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(AuthAdminUserRepositoryInterface::class, AuthAdminUserRepository::class);
        $this->app->bind(AuthorizationContextInterface::class, UseCaseAuthorizationContext::class);

        $this->app->scoped(AuthContext::class);

        $this->app->bind(
            JwtConfig::class,
            fn (): JwtConfig => new JwtConfig(
                config()->string('auth.jwt.alg'),
                config()->string('auth.jwt.key'),
                config()->string('app.url'),
            ),
        );
    }

    public function boot(): void
    {
        Auth::provider('custom', fn () => $this->app->make(AuthUserProvider::class));
    }
}
