<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Media;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use Media\Application\UseCase\Create\CreateOutputData;
use OpenAPI\Client\Model\MediaCreateResponse;
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
                new MediaCreateResponse()->setMedia($this->converter->toOpenApiMedia($outputData->media)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
