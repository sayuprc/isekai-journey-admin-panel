<?php

declare(strict_types=1);

namespace App\Features\Song\Adapter\Web\Presenters;

use App\Features\Song\Domain\Entities\Song;

class ListViewSong
{
    public function __construct(private readonly Song $song)
    {
    }

    public function songId(): string
    {
        return $this->song->songId->value;
    }

    public function title(): string
    {
        return $this->song->title->value;
    }

    public function description(): string
    {
        return $this->song->description->value;
    }

    public function orderNo(): int
    {
        return $this->song->orderNo->value;
    }

    public function releasedOn(): string
    {
        return $this->song->releasedOn->value->format('Y-m-d');
    }
}
