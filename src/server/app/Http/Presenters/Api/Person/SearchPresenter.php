<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Person;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PersonSearchResponse;
use Person\Application\UseCase\Search\SearchOutputData;
use Person\Domain\Models\Person;
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
                new PersonSearchResponse()->setPersons(
                    array_map(
                        fn (Person $person) => $this->converter->toOpenApiPerson($person),
                        $outputData->persons,
                    ),
                )->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
