<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Release;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use DateTime;
use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\MediumFormat as OpenApiMediumFormat;
use OpenAPI\Viewer\Client\Model\MediumFormatValue;
use OpenAPI\Viewer\Client\Model\ReleaseGroupListItem as OpenApiReleaseGroupListItem;
use OpenAPI\Viewer\Client\Model\ReleaseGroupListResponse;
use OpenAPI\Viewer\Client\Model\ReleaseGroupType as OpenApiReleaseGroupType;
use OpenAPI\Viewer\Client\Model\ReleaseGroupTypeValue;
use OpenAPI\Viewer\Client\Model\ReleaseListItem as OpenApiReleaseListItem;
use OpenAPI\Viewer\Client\Model\ReleaseMediumItem as OpenApiReleaseMediumItem;
use OpenAPI\Viewer\Client\Model\ReleaseTrackItem as OpenApiReleaseTrackItem;
use Release\Application\Viewer\Query\ReleaseGroupListItem;
use Release\Application\Viewer\Query\ReleaseListItem;
use Release\Application\Viewer\Query\ReleaseMediumItem;
use Release\Application\Viewer\Query\ReleaseTrackItem;
use Release\Application\Viewer\UseCase\List\ListOutputData;
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
                new ReleaseGroupListResponse(['next_cursor' => $outputData->nextCursor])
                    ->setReleaseGroups(array_map($this->toOpenApiReleaseGroupListItem(...), $outputData->releaseGroups)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }

    private function toOpenApiReleaseGroupListItem(ReleaseGroupListItem $releaseGroup): OpenApiReleaseGroupListItem
    {
        return new OpenApiReleaseGroupListItem()
            ->setReleaseGroupId($releaseGroup->releaseGroupId)
            ->setTitle($releaseGroup->title)
            ->setType(
                new OpenApiReleaseGroupType()
                    ->setName($releaseGroup->type->getName())
                    ->setValue(ReleaseGroupTypeValue::from($releaseGroup->type->value)),
            )
            ->setDescription($releaseGroup->description)
            ->setFirstReleasedOn(new DateTime($releaseGroup->firstReleasedOn))
            ->setReleases(array_map($this->toOpenApiReleaseListItem(...), $releaseGroup->releases));
    }

    private function toOpenApiReleaseListItem(ReleaseListItem $release): OpenApiReleaseListItem
    {
        return new OpenApiReleaseListItem([
            'jacket_art_url' => $release->jacketArtUrl,
        ])
            ->setReleaseId($release->releaseId)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setDescription($release->description)
            ->setOrderNo($release->orderNo)
            ->setMedia(array_map($this->toOpenApiReleaseMediumItem(...), $release->media));
    }

    private function toOpenApiReleaseMediumItem(ReleaseMediumItem $medium): OpenApiReleaseMediumItem
    {
        return new OpenApiReleaseMediumItem()
            ->setPosition($medium->position)
            ->setFormat(
                new OpenApiMediumFormat()
                    ->setName($medium->format->getName())
                    ->setValue(MediumFormatValue::from($medium->format->value)),
            )
            ->setTracks(array_map($this->toOpenApiReleaseTrackItem(...), $medium->tracks));
    }

    private function toOpenApiReleaseTrackItem(ReleaseTrackItem $track): OpenApiReleaseTrackItem
    {
        return new OpenApiReleaseTrackItem()
            ->setTrackNo($track->trackNo)
            ->setSongId($track->songId)
            ->setTitle($track->title)
            ->setIsDisplay($track->isDisplay);
    }
}
