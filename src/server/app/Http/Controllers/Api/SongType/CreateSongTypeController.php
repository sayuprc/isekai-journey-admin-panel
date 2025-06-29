<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeCreatePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;

class CreateSongTypeController extends Controller
{
    public function handle(
        CreateInputData $inputData,
        CreateUseCaseInterface $interactor,
        SongTypeCreatePresenter $presenter
    ): JsonResponse {
        return $presenter->present($interactor->handle($inputData));
    }
}
