<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyConfig;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Auth\Infrastructures\AdminUserPasskeyRepository;
use Auth\Infrastructures\Auth\AuthAdminUserRepository;
use Auth\Infrastructures\Auth\AuthUserProvider;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use Auth\Infrastructures\PasskeyAuthenticator;
use Auth\Infrastructures\PasskeyCeremonyStore;
use Auth\Infrastructures\RandomPasskeyUserHandleGenerator;
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
        $this->app->bind(AdminUserPasskeyRepositoryInterface::class, AdminUserPasskeyRepository::class);
        $this->app->bind(PasskeyAuthenticatorInterface::class, PasskeyAuthenticator::class);
        $this->app->bind(PasskeyUserHandleGeneratorInterface::class, RandomPasskeyUserHandleGenerator::class);
        $this->app->bind(PasskeyCeremonyStoreInterface::class, PasskeyCeremonyStore::class);
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

        $this->app->bind(
            PasskeyConfig::class,
            fn (): PasskeyConfig => new PasskeyConfig(
                config()->string('auth.passkey.rp_name'),
                config()->string('auth.passkey.rp_id'),
                config()->string('auth.passkey.origin'),
                config()->integer('auth.passkey.timeout_ms', 60000),
                config()->integer('auth.passkey.ceremony_ttl_seconds', 300),
                config()->string('auth.passkey.ceremony_cache_store'),
            ),
        );
    }

    public function boot(): void
    {
        Auth::provider('custom', fn () => $this->app->make(AuthUserProvider::class));
    }
}
