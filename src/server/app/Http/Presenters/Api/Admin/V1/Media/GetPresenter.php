<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Get\GetOutputData;
use OpenAPI\Client\Model\MediaGetResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (GetOutputData $outputData) => [
                new MediaGetResponse()
                    ->setMedia($this->converter->toOpenApiMedia($outputData->media))
                    ->setSongs(array_map($this->converter->toOpenApiReferencedSong(...), $outputData->songs)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
