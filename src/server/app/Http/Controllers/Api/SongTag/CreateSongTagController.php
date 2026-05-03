<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Tag\Create\CreateInputData;
use Song\Application\UseCase\Tag\Create\CreateUseCase;

class CreateSongTagController extends Controller
{
    public function __construct(
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(CreateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
