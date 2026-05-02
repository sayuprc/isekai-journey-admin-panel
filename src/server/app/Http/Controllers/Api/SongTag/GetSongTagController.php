<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\GetPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Tag\Get\GetInputData;
use Song\Application\UseCase\Tag\Get\GetUseCase;

class GetSongTagController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $songTagId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($songTagId)));
    }
}
