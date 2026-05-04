<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Person;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PersonCreateResponse;
use Person\Application\UseCase\Create\CreateOutputData;
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
                $person = $outputData->person;

                return [
                    new PersonCreateResponse()->setPerson($this->converter->toOpenApiPerson($person)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
