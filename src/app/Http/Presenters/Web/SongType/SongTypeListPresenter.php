<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\SongType;

use App\Http\ViewModels\Web\SongType\SongTypeListView;
use SongType\Domain\Models\SongType;
use SongType\UseCases\List\ListResponse;

class SongTypeListPresenter
{
    /**
     * @return array<SongTypeListView>
     */
    public function present(ListResponse $response): array
    {
        return array_map(
            fn (SongType $songType): SongTypeListView => new SongTypeListView(
                $songType->songTypeId->value,
                $songType->songTypeName->value,
                $songType->orderNo->value,
            ),
            $response->songTypes
        );
    }
}
