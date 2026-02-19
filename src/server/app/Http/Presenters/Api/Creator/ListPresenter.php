<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Creator\Application\UseCase\List\ListOutputData;
use Creator\Domain\Models\Creator;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorListResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListOutputData $outputData) => [
                new CreatorListResponse()->setCreators(
                    array_map(
                        fn (Creator $creator) => $this->converter->toOpenApiCreator($creator),
                        $outputData->creators,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => match (true) {
                $error instanceof AuthenticationError => [[], 401],
                $error instanceof AuthorizationError => [[], 403],
                default => [
                    new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                    400,
                ],
            },
        );

        return response()->json($data, $status);
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
