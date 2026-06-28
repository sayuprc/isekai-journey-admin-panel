<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use DateTime;
use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\MediaFormat;
use OpenAPI\Viewer\Client\Model\MediaFormatValue;
use OpenAPI\Viewer\Client\Model\MediaPlatform;
use OpenAPI\Viewer\Client\Model\MediaPlatformValue;
use OpenAPI\Viewer\Client\Model\MediaType;
use OpenAPI\Viewer\Client\Model\MediaTypeValue;
use OpenAPI\Viewer\Client\Model\SongListItem as OpenApiSongListItem;
use OpenAPI\Viewer\Client\Model\SongListResponse;
use OpenAPI\Viewer\Client\Model\SongMediaSummary as OpenApiSongMediaSummary;
use OpenAPI\Viewer\Client\Model\SongRelationCounts;
use OpenAPI\Viewer\Client\Model\SongType;
use OpenAPI\Viewer\Client\Model\SongTypeValue;
use ResultType\Result;
use Song\Application\Viewer\Query\SongListItem;
use Song\Application\Viewer\Query\SongMediaSummary;
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
                new SongListResponse(['next_cursor' => $outputData->nextCursor])
                    ->setSongs(array_map($this->toOpenApiSongListItem(...), $outputData->songs)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    private function toOpenApiSongListItem(SongListItem $song): OpenApiSongListItem
    {
        $media = array_map($this->toOpenApiSongMediaSummary(...), $song->media);

        return new OpenApiSongListItem()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType(new SongType()->setName($song->type->getName())->setValue(SongTypeValue::from($song->type->value)))
            ->setLyricists($song->lyricists)
            ->setComposers($song->composers)
            ->setArrangers($song->arrangers)
            ->setCounts(new SongRelationCounts()->setMediaCount(count($media)))
            ->setMedia($media);
    }

    private function toOpenApiSongMediaSummary(SongMediaSummary $media): OpenApiSongMediaSummary
    {
        return new OpenApiSongMediaSummary()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setType(new MediaType()->setName($media->type->getName())->setValue(MediaTypeValue::from($media->type->value)))
            ->setFormat(new MediaFormat()->setName($media->format->getName())->setValue(MediaFormatValue::from($media->format->value)))
            ->setPlatform(new MediaPlatform()->setName($media->platform->getName())->setValue(MediaPlatformValue::from($media->platform->value)))
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt));
    }
}
