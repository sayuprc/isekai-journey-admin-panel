<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Creator\Application\UseCase\Get\GetOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorGetResponse;
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
                    new CreatorGetResponse()->setCreator($this->converter->toOpenApiCreator($outputData->creator)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
