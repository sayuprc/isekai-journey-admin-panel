<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ReleaseCreateResponse;
use Release\Application\Admin\UseCase\Create\CreateOutputData;
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
            fn (CreateOutputData $outputData) => [
                new ReleaseCreateResponse()->setRelease(
                    $this->converter->toOpenApiRelease($outputData->release),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
