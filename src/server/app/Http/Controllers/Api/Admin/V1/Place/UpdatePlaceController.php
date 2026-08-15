<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Place;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Place\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Place\Application\Admin\UseCase\Update\UpdateInputData;
use Place\Application\Admin\UseCase\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdatePlaceController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $placeId, Request $request): JsonResponse
    {
        return $this->buildInput($placeId, $request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(string $placeId, Request $request): UpdateInputData
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'placeId' => $placeId,
            ],
        );
    }
}
