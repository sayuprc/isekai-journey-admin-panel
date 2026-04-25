<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use OpenAPI\Client\Model\Arranger as OpenApiArranger;
use OpenAPI\Client\Model\Composer as OpenApiComposer;
use OpenAPI\Client\Model\Lyricist as OpenApiLyricist;
use OpenAPI\Client\Model\Song as OpenApiSong;
use OpenAPI\Client\Model\SongAttribute as OpenApiSongAttribute;
use OpenAPI\Client\Model\SongAttributeValue;
use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use Song\Application\Assemble\AssembledCreator;
use Song\Application\Assemble\AssembledSong;

readonly class Converter
{
    public function toOpenApiSong(AssembledSong $song): OpenApiSong
    {
        $openApiSong = new OpenApiSong();

        $openApiSong
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType($this->toOpenApiSongType($song))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo)
            ->setLyricists(array_map($this->toOpenApiLyricist(...), $song->lyricists))
            ->setComposers(array_map($this->toOpenApiComposer(...), $song->composers))
            ->setArrangers(array_map($this->toOpenApiArranger(...), $song->arrangers));

        if ($song->hasAttribute()) {
            $openApiSong->setAttribute($this->toOpenApiSongAttribute($song->attributeName, $song->attributeValue));
        }

        return $openApiSong;
    }

    private function toOpenApiSongType(AssembledSong $song): OpenApiSongType
    {
        $type = new OpenApiSongType();

        return $type
            ->setName($song->typeName)
            ->setValue(SongTypeValue::from($song->typeValue));
    }

    private function toOpenApiSongAttribute(string $name, int $value): OpenApiSongAttribute
    {
        $attribute = new OpenApiSongAttribute();

        return $attribute
            ->setName($name)
            ->setValue(SongAttributeValue::from($value));
    }

    private function toOpenApiLyricist(AssembledCreator $creator): OpenApiLyricist
    {
        $lyricist = new OpenApiLyricist();

        return $lyricist
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }

    private function toOpenApiComposer(AssembledCreator $creator): OpenApiComposer
    {
        $composer = new OpenApiComposer();

        return $composer
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }

    private function toOpenApiArranger(AssembledCreator $creator): OpenApiArranger
    {
        $arranger = new OpenApiArranger();

        return $arranger
            ->setCreatorId($creator->creatorId)
            ->setName($creator->name)
            ->setOrderNo($creator->orderNo);
    }
}
