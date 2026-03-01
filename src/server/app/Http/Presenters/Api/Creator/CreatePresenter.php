<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Creator\Application\UseCase\Create\CreateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorCreateResponse;
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
                $creator = $outputData->creator;

                return [
                    new CreatorCreateResponse()->setCreator($this->converter->toOpenApiCreator($creator)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
