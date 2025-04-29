<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\SongType;

use App\Http\ViewModels\Web\SongType\SongTypeView;
use SongType\UseCases\Get\GetResponse;

class SongTypePresenter
{
    public function present(GetResponse $response): SongTypeView
    {
        return new SongTypeView(
            $response->songType->songTypeId->value,
            $response->songType->songTypeName->value,
            $response->songType->orderNo->value,
        );
    }
}
