<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeDeletePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;

class DeleteSongTypeController extends Controller
{
    public function handle(
        string $songTypeId,
        DeleteUseCaseInterface $interactor,
        SongTypeDeletePresenter $presenter
    ): JsonResponse {
        $interactor->handle(new DeleteInputData($songTypeId));

        return $presenter->present();
    }
}
