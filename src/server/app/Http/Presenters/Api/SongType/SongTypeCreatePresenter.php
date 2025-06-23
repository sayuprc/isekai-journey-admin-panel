<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\CreateSongTypeResponse;
use OpenAPI\Client\Model\SongType;
use SongType\Application\UseCase\Create\CreateOutputData;

class SongTypeCreatePresenter
{
    public function present(CreateOutputData $outputData): CreateSongTypeResponse
    {
        return new CreateSongTypeResponse()
            ->setSongType(
                new SongType()->setSongTypeId($outputData->songType->songTypeId->value)
                    ->setSongTypeName($outputData->songType->songTypeName->value)
                    ->setOrderNo($outputData->songType->orderNo->value)
            );
    }
}
