<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Person;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PersonListResponse;
use Person\Application\UseCase\List\ListOutputData;
use Person\Domain\Models\Person;
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
                new PersonListResponse()->setPersons(
                    array_map(
                        fn (Person $person) => $this->converter->toOpenApiPerson($person),
                        $outputData->persons,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
