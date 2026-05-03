<?php

declare(strict_types=1);

namespace Support\UseCase\Authorizer;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\UseCaseError;

readonly class UseCaseAuthorizer
{
    public function __construct(private AuthorizationContextInterface $context)
    {
    }

    /**
     * @return Result<AuthorizableUserInterface, UseCaseError>
     */
    public function require(Permission $permission): Result
    {
        $user = $this->context->currentUser();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can($permission)) {
            return new Err(new AuthorizationError());
        }

        return new Ok($user);
    }
}
