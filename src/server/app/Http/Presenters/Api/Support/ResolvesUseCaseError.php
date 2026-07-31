<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Support;

use App\Http\Responses\ApiError;
use OpenAPI\Admin\Client\Model\ErrorResponse;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

trait ResolvesUseCaseError
{
    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    private function resolveError(UseCaseError $error): array
    {
        return match (true) {
            $error instanceof AuthenticationError => ApiError::unauthenticated(),
            $error instanceof AuthorizationError => ApiError::permissionDenied(),
            $error instanceof NotFoundError => ApiError::notFound(
                sprintf('%sが見つかりません: %s', $error->resourceName, $error->identifier),
            ),
            $error instanceof InvalidInputError => ApiError::validationFailed($error->errors),
            $error instanceof BusinessLogicError => ApiError::businessRuleViolation($error->message),
            default => ApiError::internalError(),
        };
    }
}
