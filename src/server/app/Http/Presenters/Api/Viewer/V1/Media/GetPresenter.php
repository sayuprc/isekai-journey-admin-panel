<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Media;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use Media\Application\Viewer\Query\MediaDetail;
use Media\Application\Viewer\Query\MediaDetailSongSummary;
use Media\Application\Viewer\UseCase\Get\GetOutputData;
use ResultType\Result;
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
                ['media' => $this->toArray($outputData->media)],
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    /**
     * @return array{mediaId: string, title: string, url: string, publishedAt: string, type: array{name: string, value: 1|2|3|4|99}, format: array{name: string, value: 1|2|3|4|5|99}, counts: array{songCount: int}, songs: array<array{songId: string, title: string, type: array{name: string, value: 1|2}}>}
     */
    private function toArray(MediaDetail $media): array
    {
        $songs = array_map($this->toSongArray(...), $media->songs);

        return [
            'mediaId' => $media->mediaId,
            'title' => $media->title,
            'url' => $media->url,
            'publishedAt' => $media->publishedAt->format('Y-m-d'),
            'type' => [
                'name' => $media->type->getName(),
                'value' => $media->type->value,
            ],
            'format' => [
                'name' => $media->format->getName(),
                'value' => $media->format->value,
            ],
            'counts' => [
                'songCount' => count($songs),
            ],
            'songs' => $songs,
        ];
    }

    /**
     * @return array{songId: string, title: string, type: array{name: string, value: 1|2}}
     */
    private function toSongArray(MediaDetailSongSummary $song): array
    {
        return [
            'songId' => $song->songId,
            'title' => $song->title,
            'type' => [
                'name' => $song->type->getName(),
                'value' => $song->type->value,
            ],
        ];
    }
}
