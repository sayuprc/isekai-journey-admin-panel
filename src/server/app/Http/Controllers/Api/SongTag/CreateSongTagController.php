<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Song\Application\UseCase\Tag\Create\CreateInputData;
use Song\Application\UseCase\Tag\Create\CreateUseCase;
use Support\Contracts\MapperInterface;

class CreateSongTagController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(CreateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
