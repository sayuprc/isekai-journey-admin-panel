<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use OpenAPI\Client\Model\Arranger as OpenApiArranger;
use OpenAPI\Client\Model\Composer as OpenApiComposer;
use OpenAPI\Client\Model\Lyricist as OpenApiLyricist;
use OpenAPI\Client\Model\Song as OpenApiSong;
use OpenAPI\Client\Model\SongAttachedTag as OpenApiSongAttachedTag;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\AssembledTag;

readonly class Converter
{
    public function toOpenApiSong(AssembledSong $song): OpenApiSong
    {
        return new OpenApiSong()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType($this->toOpenApiSongType($song))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo)
            ->setLyricists(array_map($this->toOpenApiLyricist(...), $song->lyricists))
            ->setComposers(array_map($this->toOpenApiComposer(...), $song->composers))
            ->setArrangers(array_map($this->toOpenApiArranger(...), $song->arrangers))
            ->setTags(array_map($this->toOpenApiSongTag(...), $song->tags));
    }

    private function toOpenApiSongType(AssembledSong $song): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($song->typeName)
            ->setValue(SongTypeValue::from($song->typeValue));
    }

    private function toOpenApiLyricist(AssembledCreator $creator): OpenApiLyricist
    {
        return new OpenApiLyricist()
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

    private function toOpenApiArranger(AssembledCreator $creator): OpenApiArranger
    {
        return new OpenApiArranger()
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }

    private function toOpenApiSongTag(AssembledTag $tag): OpenApiSongAttachedTag
    {
        return new OpenApiSongAttachedTag()
            ->setSongTagId($tag->songTagId)
            ->setName($tag->name);
    }
}
