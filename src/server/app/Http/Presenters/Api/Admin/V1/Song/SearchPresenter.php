<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongSearchResponse;
use OpenAPI\Client\Model\SongSummary as OpenApiSongSummary;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use ResultType\Result;
use Song\Application\Admin\Query\SongSummary;
use Song\Application\Admin\UseCase\Search\SearchOutputData;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\UseCaseError;

class SearchPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<SearchOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (SearchOutputData $outputData) => [
                new SongSearchResponse()
                    ->setSongs(array_map($this->toOpenApiSongSummary(...), $outputData->songs))
                    ->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    private function toOpenApiSongSummary(SongSummary $song): OpenApiSongSummary
    {
        return new OpenApiSongSummary()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setType($this->toOpenApiSongType($song->type))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo);
    }

    private function toOpenApiSongType(SongType $type): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($type->getName())
            ->setValue(SongTypeValue::from($type->value));
    }
}
