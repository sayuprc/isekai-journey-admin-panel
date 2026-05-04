<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use OpenAPI\Client\Model\Song as OpenApiSong;
use OpenAPI\Client\Model\SongAttachedTag as OpenApiSongAttachedTag;
use OpenAPI\Client\Model\SongPerson as OpenApiSongPerson;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use Song\Application\Assemble\AssembledPerson;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\AssembledTag;

readonly class Converter
{
    public function toOpenApiSong(AssembledSong $song): OpenApiSong
    {
        return new OpenApiSong(['lyrics_link' => $song->lyricsLink])
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType($this->toOpenApiSongType($song))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo)
            ->setPersons(array_map($this->toOpenApiSongPerson(...), $song->persons))
            ->setTags(array_map($this->toOpenApiSongTag(...), $song->tags));
    }

    private function toOpenApiSongType(AssembledSong $song): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($song->typeName)
            ->setValue(SongTypeValue::from($song->typeValue));
    }

    private function toOpenApiSongPerson(AssembledPerson $person): OpenApiSongPerson
    {
        return new OpenApiSongPerson()
            ->setPersonId($person->personId)
            ->setName($person->name)
            ->setRole($person->role)
            ->setOrderNo($person->orderNo);
    }

    private function toOpenApiSongTag(AssembledTag $tag): OpenApiSongAttachedTag
    {
        return new OpenApiSongAttachedTag()
            ->setSongTagId($tag->songTagId)
            ->setName($tag->name);
    }
}
