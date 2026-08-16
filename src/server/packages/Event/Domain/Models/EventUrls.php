<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * @extends ImmutableCollection<int, EventUrl>
 */
readonly class EventUrls extends ImmutableCollection
{
    /**
     * @param list<array{url: string, orderNo: int, label?: ?string}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $urls = [];
        $seenOrder = [];

        foreach ($items as $item) {
            $url = EventUrl::reconstruct(
                $item['url'],
                $item['orderNo'],
                $item['label'] ?? null,
            );

            if (isset($seenOrder[$url->orderNo->value])) {
                throw new BusinessRuleViolationException('URL の表示順が重複しています。');
            }

            $seenOrder[$url->orderNo->value] = true;
            $urls[] = $url;
        }

        return new self($urls);
    }

    /**
     * @param list<array{url: string, orderNo: int, label?: ?string}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventUrl => EventUrl::reconstruct(
                $item['url'],
                $item['orderNo'],
                $item['label'] ?? null,
            ),
            $items,
        ));
    }

    /**
     * @return list<array{url: string, order_no: int, label: ?string}>
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
