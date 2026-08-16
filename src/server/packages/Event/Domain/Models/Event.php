<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use DateTimeImmutable;
use DateTimeZone;
use Support\Domain\Exceptions\InvalidDomainException;

readonly class Event
{
    public function __construct(
        public EventId $eventId,
        public EventTitle $title,
        public EventType $type,
        public EventStartedAt $startedAt,
        public EventEndedAt $endedAt,
        public Description $description,
        public bool $isDisplay,
        public EventPlaceLinks $places,
        public EventUrls $urls,
    ) {
        if ($this->startedAt->value > $this->endedAt->value) {
            throw new InvalidDomainException('開始日時は終了日時以前である必要があります');
        }
    }

    /**
     * @param list<array{placeId: string}>                            $places
     * @param list<array{url: string, orderNo: int, label?: ?string}> $urls
     */
    public static function reconstruct(
        string $eventId,
        string $title,
        int $type,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $endedAt,
        string $description,
        bool $isDisplay,
        array $places,
        array $urls,
    ): self {
        return new self(
            new EventId($eventId),
            new EventTitle($title),
            EventType::from($type),
            new EventStartedAt($startedAt),
            new EventEndedAt($endedAt),
            new Description($description),
            $isDisplay,
            EventPlaceLinks::reconstruct($places),
            EventUrls::reconstruct($urls),
        );
    }

    /**
     * @return array{event_id: string, title: string, type: value-of<EventType>, started_at: string, ended_at: string, description: string, is_display: bool, places: list<array{place_id: string}>, urls: list<array{url: string, order_no: int, label: ?string}>}
     */
    public function toArray(): array
    {
        $timezone = new DateTimeZone(date_default_timezone_get());

        return [
            'event_id' => $this->eventId->value,
            'title' => $this->title->value,
            'type' => $this->type->value,
            'started_at' => $this->startedAt->value
                ->setTimezone($timezone)
                ->format('Y-m-d H:i:s'),
            'ended_at' => $this->endedAt->value
                ->setTimezone($timezone)
                ->format('Y-m-d H:i:s'),
            'description' => $this->description->value,
            'is_display' => $this->isDisplay,
            'places' => $this->places->toArray(),
            'urls' => $this->urls->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->eventId->equals($other->eventId);
    }
}
