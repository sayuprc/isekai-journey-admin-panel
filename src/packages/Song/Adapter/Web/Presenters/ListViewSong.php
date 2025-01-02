<?php

declare(strict_types=1);

namespace Song\Adapter\Web\Presenters;

use Exception;
use Song\Domain\Entities\Song;

/**
 * @property string $songId
 * @property string $title
 * @property string $description
 * @property string $releasedOn
 * @property int    $orderNo
 */
class ListViewSong
{
    public function __construct(private readonly Song $song)
    {
    }

    public function __get(string $name)
    {
        if (in_array($name, ['songId', 'title', 'description', 'orderNo'], true)) {
            return $this->song->{$name}->value;
        } elseif ($name === 'releasedOn') {
            return $this->releasedOn();
        }

        throw new Exception('Invalid property: ' . $name);
    }

    private function releasedOn(): string
    {
        return $this->song->releasedOn->value->format('Y-m-d');
    }
}
