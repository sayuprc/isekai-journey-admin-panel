<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Place\Domain\Models\PlaceId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * @extends ImmutableCollection<int, EventPlaceLink>
 */
readonly class EventPlaceLinks extends ImmutableCollection
{
    /**
     * @param list<array{placeId: string}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $links = [];
        $seen = [];

        foreach ($items as $item) {
            $link = new EventPlaceLink(new PlaceId($item['placeId']));

            if (isset($seen[$link->placeId->value])) {
                throw new BusinessRuleViolationException('同じ場所を複数指定することはできません。');
            }

            $seen[$link->placeId->value] = true;
            $links[] = $link;
        }

        return new self($links);
    }

    /**
     * @param list<array{placeId: string}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventPlaceLink => EventPlaceLink::reconstruct($item['placeId']),
            $items,
        ));
    }

    /**
     * @return list<array{place_id: string}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
