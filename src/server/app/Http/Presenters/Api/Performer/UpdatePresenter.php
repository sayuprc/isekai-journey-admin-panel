<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\PerformerUpdateResponse;
use Performer\Application\UseCase\Update\UpdateOutputData;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<UpdateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (UpdateOutputData $outputData) {
                $performer = $outputData->performer;

                return [
                    new PerformerUpdateResponse()->setPerformer($this->converter->toOpenApiPerformer($performer)),
                    200,
                ];
            },
            function (UseCaseError $error) {
                return [
                    new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                    400,
                ];
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
