<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\GetSongTypeResponse;
use OpenAPI\Client\Model\SongType;
use SongType\Application\UseCase\Get\GetOutputData;

class SongTypeGetPresenter
{
    public function present(GetOutputData $outputData): GetSongTypeResponse
    {
        return new GetSongTypeResponse()
            ->setSongType(
                new SongType()->setSongTypeId($outputData->songType->songTypeId->value)
                    ->setSongTypeName($outputData->songType->songTypeName->value)
                    ->setOrderNo($outputData->songType->orderNo->value)
            );
    }
}
