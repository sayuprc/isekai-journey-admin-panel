<?php

declare(strict_types=1);

namespace Place\Domain\Models;

readonly class Place
{
    public function __construct(
        public PlaceId $placeId,
        public PlaceName $name,
        public PlaceKind $kind,
    ) {
    }

    public static function reconstruct(string $placeId, string $name, int $kind): self
    {
        return new self(
            new PlaceId($placeId),
            new PlaceName($name),
            PlaceKind::from($kind),
        );
    }

    /**
     * @return array{place_id: string, name: string, kind: value-of<PlaceKind>}
     */
    public function toArray(): array
    {
        return [
            'place_id' => $this->placeId->value,
            'name' => $this->name->value,
            'kind' => $this->kind->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->placeId->equals($other->placeId);
    }
}
