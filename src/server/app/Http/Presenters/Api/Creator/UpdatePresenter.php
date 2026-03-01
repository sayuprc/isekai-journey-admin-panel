<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorUpdateResponse;
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
            function (UpdateOutputData $outputData) {
                $creator = $outputData->creator;

                return [
                    new CreatorUpdateResponse()->setCreator($this->converter->toOpenApiCreator($creator)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
