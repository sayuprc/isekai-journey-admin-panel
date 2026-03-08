<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Creator\Application\UseCase\Search\SearchOutputData;
use Creator\Domain\Models\Creator;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorSearchResponse;
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
                new CreatorSearchResponse()->setCreators(
                    array_map(
                        fn (Creator $creator) => $this->converter->toOpenApiCreator($creator),
                        $outputData->creators,
                    ),
                )->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
