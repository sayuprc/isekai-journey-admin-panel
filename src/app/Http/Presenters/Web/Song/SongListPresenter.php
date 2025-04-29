<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\Song;

use App\Http\ViewModels\Web\Song\SongListView;
use Song\Domain\Models\Song;
use Song\UseCases\List\ListOutputData;

class SongListPresenter
{
    /**
     * @return array<SongListView>
     */
    public function present(ListOutputData $outputData): array
    {
        return array_map(function (Song $song): SongListView {
            return new SongListView(
                $song->songId->value,
                $song->title->value,
                $song->description->value,
                $song->orderNo->value,
            );
        }, $outputData->songs);
    }
}
