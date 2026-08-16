<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Place\Domain\Models\PlaceId;

readonly class EventPlaceLink
{
    public function __construct(public PlaceId $placeId)
    {
    }

    public static function reconstruct(string $placeId): self
    {
        return new self(new PlaceId($placeId));
    }

    /**
     * @return array{place_id: string}
     */
    public function toArray(): array
    {
        return [
            'place_id' => $this->placeId->value,
        ];
    }
}
