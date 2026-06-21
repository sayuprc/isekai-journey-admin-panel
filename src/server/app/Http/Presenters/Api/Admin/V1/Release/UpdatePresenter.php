<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseUpdateResponse;
use Release\Application\Admin\UseCase\Update\UpdateOutputData;
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
                new ReleaseUpdateResponse()->setRelease(
                    $this->converter->toOpenApiRelease($outputData->release),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
