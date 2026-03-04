<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongAttribute;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongAttributeListResponse;
use ResultType\Result;
use Song\Domain\Models\SongAttribute;
use SongAttribute\Application\UseCase\List\ListOutputData;
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
                new SongAttributeListResponse()->setSongAttributes(
                    array_map(
                        fn (SongAttribute $songAttribute) => $this->converter->toOpenApiSongAttribute($songAttribute),
                        $outputData->songAttributes,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
