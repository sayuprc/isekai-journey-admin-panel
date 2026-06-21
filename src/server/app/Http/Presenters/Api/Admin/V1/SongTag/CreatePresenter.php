<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagCreateResponse;
use ResultType\Result;
use Song\Application\Admin\UseCase\Tag\Create\CreateOutputData;
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
                new SongTagCreateResponse()->setTag($this->converter->toOpenApiSongTag($outputData->tag)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
