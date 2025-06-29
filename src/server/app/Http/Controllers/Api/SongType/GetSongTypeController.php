<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeGetPresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Get\GetInputData;
use SongType\Application\UseCase\Get\GetUseCaseInterface;

class GetSongTypeController extends Controller
{
    public function handle(string $songTypeId, GetUseCaseInterface $interactor, SongTypeGetPresenter $presenter): JsonResponse
    {
        return $presenter->present($interactor->handle(new GetInputData($songTypeId)));
    }
}
