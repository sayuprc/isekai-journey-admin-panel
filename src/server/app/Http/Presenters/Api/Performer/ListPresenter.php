<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PerformerListResponse;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\Domain\Models\Performer;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

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
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
