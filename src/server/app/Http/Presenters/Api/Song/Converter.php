<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use OpenAPI\Client\Model\Arranger as OpenApiArranger;
use OpenAPI\Client\Model\Composer as OpenApiComposer;
use OpenAPI\Client\Model\Lyricist as OpenApiLyricist;
use OpenAPI\Client\Model\Song as OpenApiSong;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;

readonly class Converter
{
    public function toOpenApiSong(AssembledSong $song): OpenApiSong
    {
        return new OpenApiSong()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setSongType($this->toOpenApiSongType($song))
            ->setOrderNo($song->orderNo)
            ->setArrangers(array_map($this->toOpenApiArranger(...), $song->arrangers))
            ->setComposers(array_map($this->toOpenApiComposer(...), $song->composers))
            ->setLyricists(array_map($this->toOpenApiLyricist(...), $song->lyricists));
    }

    private function toOpenApiSongType(AssembledSong $song): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($song->songTypeName)
            ->setValue(SongTypeValue::from($song->songTypeValue));
    }

    private function toOpenApiArranger(AssembledCreator $creator): OpenApiArranger
    {
        return new OpenApiArranger()
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }

    private function toOpenApiComposer(AssembledCreator $creator): OpenApiComposer
    {
        return new OpenApiComposer()
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }

    private function toOpenApiLyricist(AssembledCreator $creator): OpenApiLyricist
    {
        return new OpenApiLyricist()
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }
}
