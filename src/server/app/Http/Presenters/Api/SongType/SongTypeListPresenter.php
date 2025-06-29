<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ListSongTypesResponse;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\Domain\Models\SongType;

class SongTypeListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ListSongTypesResponse()
                ->setSongTypes(
                    array_map(
                        fn (SongType $songType) => $this->converter->toOpenApiSongType($songType),
                        $outputData->songTypes
                    )
                ),
            200
        );
    }
}
