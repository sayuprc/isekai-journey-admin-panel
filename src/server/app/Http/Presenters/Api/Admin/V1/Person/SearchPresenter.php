<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonSearchResponse;
use Person\Application\Admin\UseCase\Search\SearchOutputData;
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
                new PersonSearchResponse()
                    ->setPersons(array_map($this->converter->toOpenApiPerson(...), $outputData->persons))
                    ->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
