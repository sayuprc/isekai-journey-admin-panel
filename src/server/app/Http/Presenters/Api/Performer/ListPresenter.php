<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\PerformerListResponse;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\Domain\Models\Performer;
use ResultType\Result;
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
                new PerformerListResponse()->setPerformers(
                    array_map(
                        fn (Performer $performer) => $this->converter->toOpenApiPerformer($performer),
                        $outputData->performers,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => [
                new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                400,
            ],
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
