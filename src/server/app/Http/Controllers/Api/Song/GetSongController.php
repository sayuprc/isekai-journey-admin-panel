<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Song\GetPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Get\GetInputData;
use Song\Application\UseCase\Get\GetUseCase;

class GetSongController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $songId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($songId)));
    }
}
