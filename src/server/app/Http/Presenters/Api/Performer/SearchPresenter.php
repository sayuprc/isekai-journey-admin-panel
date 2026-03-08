<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PerformerSearchResponse;
use Performer\Application\UseCase\Search\SearchOutputData;
use Performer\Domain\Models\Performer;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class SearchPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<SearchOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (SearchOutputData $outputData) => [
                new PerformerSearchResponse()->setPerformers(
                    array_map(
                        fn (Performer $performer) => $this->converter->toOpenApiPerformer($performer),
                        $outputData->performers,
                    ),
                )->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
