<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Support;

use OpenAPI\Admin\Client\Model\ErrorResponse;
use OpenAPI\Admin\Client\Model\ValidationError;
use OpenAPI\Admin\Client\Model\ValidationErrorDetail;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

trait ResolvesUseCaseError
{
    /**
     * @return array{0: mixed, 1: int}
     */
    private function resolveError(UseCaseError $error): array
    {
        return match (true) {
            $error instanceof AuthenticationError => [[], 401],
            $error instanceof AuthorizationError => [[], 403],
            $error instanceof NotFoundError => [
                new ErrorResponse()->setMessage(
                    sprintf('%sが見つかりません: %s', $error->resourceName, $error->identifier),
                ),
                404,
            ],
            $error instanceof InvalidInputError => [
                $this->toValidationError($error),
                422,
            ],
            $error instanceof BusinessLogicError => [
                new ErrorResponse()->setMessage($error->message),
                400,
            ],
            default => [
                new ErrorResponse()->setMessage('予期しないエラーが発生しました'),
                500,
            ],
        };
    }

    private function toValidationError(InvalidInputError $error): ValidationError
    {
        $details = [];

        foreach ($error->errors as $field => $messages) {
            foreach ($messages as $message) {
                $details[] = new ValidationErrorDetail()
                    ->setField($field)
                    ->setMessage($message);
            }
        }

        return new ValidationError()->setErrors($details);
    }
}
