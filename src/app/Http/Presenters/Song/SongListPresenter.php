<?php

declare(strict_types=1);

namespace App\Http\Presenters\Song;

use App\Http\ViewModels\Song\SongListView;
use Song\Domain\Entities\Song;

class SongListPresenter
{
    public function __construct(private readonly Song $song)
    {
    }

    public function present(): SongListView
    {
        return new SongListView(
            $this->song->songId->value,
            $this->song->title->value,
            $this->song->description->value,
            $this->song->orderNo->value,
            $this->song->releasedOn->value->format('Y-m-d'),
        );
    }
}
