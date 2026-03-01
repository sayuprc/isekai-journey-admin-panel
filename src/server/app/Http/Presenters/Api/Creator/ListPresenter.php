<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Creator\Application\UseCase\List\ListOutputData;
use Creator\Domain\Models\Creator;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorListResponse;
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
                new CreatorListResponse()->setCreators(
                    array_map(
                        fn (Creator $creator) => $this->converter->toOpenApiCreator($creator),
                        $outputData->creators,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
