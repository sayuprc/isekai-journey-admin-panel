<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Place;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Place\GetPresenter;
use Illuminate\Http\JsonResponse;
use Place\Application\Admin\UseCase\Get\GetInputData;
use Place\Application\Admin\UseCase\Get\GetUseCase;

class GetPlaceController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $placeId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($placeId)));
    }
}
