<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PerformerGetResponse;
use Performer\Application\UseCase\Get\GetOutputData;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

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
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
