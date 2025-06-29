<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\GetPresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Get\GetInputData;
use SongType\Application\UseCase\Get\GetUseCaseInterface;

class GetSongTypeController extends Controller
{
    public function __construct(
        private readonly GetUseCaseInterface $interactor,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $songTypeId): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle(new GetInputData($songTypeId)));
    }
}
