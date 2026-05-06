<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Auth\Application\UseCase\Refresh\RefreshInputData;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Auth\Infrastructures\AdminUserPasskeyRepository;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use Auth\Infrastructures\PasskeyAuthenticator;
use Auth\Infrastructures\PasskeyCeremonyStore;
use Auth\Infrastructures\Token\AccessToken\AccessTokenFactory;
use Auth\Infrastructures\Token\AccessToken\JwtHandler;
use Auth\Infrastructures\Token\RefreshToken\RandomTokenGenerator;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Auth\Infrastructures\Token\RefreshToken\TokenHasher;
use Illuminate\Http\Request;
use Override;
use Support\UseCase\Authorizer\AuthorizationContextInterface;

class AuthServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(JwtHandlerInterface::class, JwtHandler::class);
        $this->app->bind(AccessTokenFactoryInterface::class, AccessTokenFactory::class);
        $this->app->bind(RandomTokenGeneratorInterface::class, RandomTokenGenerator::class);
        $this->app->bind(TokenHasherInterface::class, TokenHasher::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(AdminUserPasskeyRepositoryInterface::class, AdminUserPasskeyRepository::class);
        $this->app->bind(PasskeyCeremonyStoreInterface::class, PasskeyCeremonyStore::class);
        $this->app->bind(PasskeyAuthenticatorInterface::class, PasskeyAuthenticator::class);
        $this->app->bind(AuthorizationContextInterface::class, UseCaseAuthorizationContext::class);
        $this->app->bind(RefreshInputData::class, function (): RefreshInputData {
            $request = $this->app->make(Request::class);

            return new RefreshInputData(
                $request->string('refreshTokenId')->toString(),
                $request->string('refreshToken')->toString(),
            );
        });

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
}
