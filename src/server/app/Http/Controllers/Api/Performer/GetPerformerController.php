<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Performer;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Performer\GetPresenter;
use Illuminate\Http\JsonResponse;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetUseCase;

class GetPerformerController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $performerId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($performerId)));
    }
}
