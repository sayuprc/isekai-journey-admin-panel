<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeUpdatePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;

class UpdateSongTypeController extends Controller
{
    public function handle(
        UpdateInputData $inputData,
        UpdateUseCaseInterface $interactor,
        SongTypeUpdatePresenter $presenter
    ): JsonResponse {
        return $presenter->present($interactor->handle($inputData));
    }
}
