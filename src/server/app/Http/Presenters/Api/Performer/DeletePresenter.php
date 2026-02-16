<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class DeletePresenter
{
    /**
     * @param Result<null, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            fn () => response()->json(status: 204),
            fn (UseCaseError $error) => response()->json(
                new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                400,
            ),
        );
    }

    private function resolveErrorMessage(UseCaseError $error): string
    {
        if (! $error instanceof InvalidInputError) {
            return '';
        }

        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
