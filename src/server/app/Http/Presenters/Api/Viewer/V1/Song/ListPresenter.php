<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use ResultType\Result;
use Song\Application\Viewer\Query\SongListItem;
use Song\Application\Viewer\UseCase\List\ListOutputData;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<ListOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListOutputData $outputData) => [
                array_filter([
                    'songs' => array_map($this->toArray(...), $outputData->songs),
                    'nextCursor' => $outputData->nextCursor,
                ], static fn (mixed $value): bool => $value !== null),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    /**
     * @return array{songId: string, title: string, type: array{name: string, value: 1|2}, description: string, lyricists: array<string>, composers: array<string>, arrangers: array<string>, counts: array{releaseCount: int, mediaCount: int}}
     */
    private function toArray(SongListItem $song): array
    {
        return [
            'songId' => $song->songId,
            'title' => $song->title,
            'type' => [
                'name' => $song->type->getName(),
                'value' => $song->type->value,
            ],
            'description' => $song->description,
            'lyricists' => $song->lyricists,
            'composers' => $song->composers,
            'arrangers' => $song->arrangers,
            'counts' => [
                'releaseCount' => $song->releaseCount,
                'mediaCount' => $song->mediaCount,
            ],
        ];
    }
}
