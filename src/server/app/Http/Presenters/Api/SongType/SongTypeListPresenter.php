<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\ListSongTypeResponse;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\Domain\Models\SongType;

class SongTypeListPresenter
{
    public function present(ListOutputData $outputData): ListSongTypeResponse
    {
        return new ListSongTypeResponse()
            ->setSongTypes(
                array_map(
                    fn (SongType $songType) => new OpenApiSongType()
                        ->setSongTypeId($songType->songTypeId->value)
                        ->setSongTypeName($songType->songTypeName->value)
                        ->setOrderNo($songType->orderNo->value),
                    $outputData->songTypes
                )
            );
    }
}
