<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\PerformerGetResponse;
use Performer\Application\UseCase\Get\GetOutputData;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (GetOutputData $outputData) {
                return [
                    new PerformerGetResponse()->setPerformer($this->converter->toOpenApiPerformer($outputData->performer)),
                    200,
                ];
            },
            function (UseCaseError $error) {
                return [
                    new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                    404,
                ];
            },
        );

        return response()->json($data, $status);
    }

    private function resolveErrorMessage(UseCaseError $error): string
    {
        return match (true) {
            $error instanceof InvalidInputError => $this->firstMessage($error),
            $error instanceof NotFoundError => sprintf('共演者が見つかりません: %s', $error->identifier),
            default => '予期しないエラーが発生しました',
        };
    }

    private function firstMessage(InvalidInputError $error): string
    {
        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
