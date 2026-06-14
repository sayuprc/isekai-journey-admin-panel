<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use ResultType\Result;
use Song\Application\Viewer\Query\SongDetail;
use Song\Application\Viewer\Query\SongDetailMediaSummary;
use Song\Application\Viewer\UseCase\Get\GetOutputData;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (GetOutputData $outputData) => [
                ['song' => $this->toArray($outputData->song)],
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    /**
     * @return array{songId: string, title: string, description: string, type: array{name: string, value: 1|2}, counts: array{mediaCount: int}, lyricists: array<string>, composers: array<string>, arrangers: array<string>, media: array<array{mediaId: string, title: string, type: array{name: string, value: int}, format: array{name: string, value: int}, publishedAt: string}>}
     */
    private function toArray(SongDetail $song): array
    {
        $media = array_map($this->toMediaArray(...), $song->media);

        return [
            'songId' => $song->songId,
            'title' => $song->title,
            'description' => $song->description,
            'type' => [
                'name' => $song->type->getName(),
                'value' => $song->type->value,
            ],
            'counts' => [
                'mediaCount' => count($media),
            ],
            'lyricists' => $song->lyricists,
            'composers' => $song->composers,
            'arrangers' => $song->arrangers,
            'media' => $media,
        ];
    }

    /**
     * @return array{mediaId: string, title: string, type: array{name: string, value: int}, format: array{name: string, value: int}, publishedAt: string}
     */
    private function toMediaArray(SongDetailMediaSummary $media): array
    {
        return [
            'mediaId' => $media->mediaId,
            'title' => $media->title,
            'type' => [
                'name' => $media->type->getName(),
                'value' => $media->type->value,
            ],
            'format' => [
                'name' => $media->format->getName(),
                'value' => $media->format->value,
            ],
            'publishedAt' => $media->publishedAt->format('Y-m-d'),
        ];
    }
}
