<?php

declare(strict_types=1);

namespace App\Http\Presenters\Song;

use App\Http\ViewModels\Song\SongListView;
use Song\Domain\Entities\Song;
use Song\UseCases\List\ListResponse;

class SongListPresenter
{
    /**
     * @return array<SongListView>
     */
    public function present(ListResponse $response): array
    {
        return array_map(function (Song $song): SongListView {
            return new SongListView(
                $song->songId->value,
                $song->title->value,
                $song->description->value,
                $song->orderNo->value,
                $song->releasedOn->value->format('Y-m-d'),
            );
        }, $response->songs);
    }
}
