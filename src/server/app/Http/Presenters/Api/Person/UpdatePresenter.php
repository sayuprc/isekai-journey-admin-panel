<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Person;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PersonUpdateResponse;
use Person\Application\UseCase\Update\UpdateOutputData;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class UpdatePresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<UpdateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (UpdateOutputData $outputData) => [
                new PersonUpdateResponse()->setPerson($this->converter->toOpenApiPerson($outputData->person)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
