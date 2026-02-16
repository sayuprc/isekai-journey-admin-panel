<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Creator\Application\UseCase\Create\CreateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorCreateResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<CreateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (CreateOutputData $outputData) {
                $creator = $outputData->creator;

                return [
                    new CreatorCreateResponse()->setCreator($this->converter->toOpenApiCreator($creator)),
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
