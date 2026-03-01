<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PerformerCreateResponse;
use Performer\Application\UseCase\Create\CreateOutputData;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class CreatePresenter
{
    use ResolvesUseCaseError;

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
                $performer = $outputData->performer;

                return [
                    new PerformerCreateResponse()->setPerformer($this->converter->toOpenApiPerformer($performer)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
