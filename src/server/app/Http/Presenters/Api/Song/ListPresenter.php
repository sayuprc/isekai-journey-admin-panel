<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongListResponse;
use Song\Application\Assemble\AssembledSong;
use Song\Application\UseCase\List\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongListResponse()
                ->setSongs(
                    array_map(
                        fn (AssembledSong $song) => $this->converter->toOpenApiSong($song),
                        $outputData->songs,
                    ),
                ),
            200,
        );
    }
}
