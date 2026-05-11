<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Media;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use Media\Application\Viewer\Query\MediaListItem;
use Media\Application\Viewer\UseCase\List\ListOutputData;
use ResultType\Result;
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
                    'media' => array_map($this->toArray(...), $outputData->media),
                    'nextCursor' => $outputData->nextCursor,
                ], static fn (mixed $value): bool => $value !== null),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    /**
     * @return array{mediaId: string, title: string, url: string, publishedAt: string, type: array{name: string, value: 1|2|3|4|99}, format: array{name: string, value: 1|2|3|4|5|99}}
     */
    private function toArray(MediaListItem $media): array
    {
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
        ];
    }
}
