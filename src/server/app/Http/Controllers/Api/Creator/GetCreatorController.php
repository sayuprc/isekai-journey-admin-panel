<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\GetPresenter;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetUseCase;
use Illuminate\Http\JsonResponse;

class GetCreatorController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $creatorId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($creatorId)));
    }
}
