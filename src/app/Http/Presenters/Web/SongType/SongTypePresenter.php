<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\SongType;

use App\Http\ViewModels\Web\SongType\SongTypeView;
use SongType\UseCases\Get\GetOutputData;

class SongTypePresenter
{
    public function present(GetOutputData $outputData): SongTypeView
    {
        return new SongTypeView(
            $outputData->songType->songTypeId->value,
            $outputData->songType->songTypeName->value,
            $outputData->songType->orderNo->value,
        );
    }
}
