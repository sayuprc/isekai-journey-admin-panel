<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\UseCase\Authenticate\AuthenticateOutputData;
use Auth\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;
use Override;

readonly class AuthenticateInteractor implements AuthenticateUseCaseInterface
{
    public function __construct(
        private JwtHandlerInterface $jwtHandler,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AdminUserRepositoryInterface $userRepository,
        private AuthContext $context,
    ) {
    }

    #[Override]
    public function handle(AuthenticateInputData $inputData): Result
    {
        return $this->jwtHandler->verify($inputData->accessToken)
            ->mapErr(function (DomainError $error): UseCaseError {
                return match (true) {
                    $error instanceof DomainValidationError => new InvalidInputError($error->errors),
                    $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
                    default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
                };
            })
            ->andThen(function (AccessTokenPayload $payload): Result {
                return RefreshTokenId::create($payload->jti)
                    ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
                    ->andThen(function (RefreshTokenId $refreshTokenId): Result {
                        $foundRefreshToken = $this->refreshTokenRepository->findActive($refreshTokenId);

                        if (is_null($foundRefreshToken)) {
                            return new Err(new NotFoundError('リフレッシュトークン', $refreshTokenId->value));
                        }

                        $foundUser = $this->userRepository->find($foundRefreshToken->adminUserId);

                        if (is_null($foundUser)) {
                            return new Err(new NotFoundError('ユーザー', $foundRefreshToken->adminUserId->value));
                        }

                        $this->context->set($foundUser);

                        return new Ok(new AuthenticateOutputData());
                    });
            });
    }
}
