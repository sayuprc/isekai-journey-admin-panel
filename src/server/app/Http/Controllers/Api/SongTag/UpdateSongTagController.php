<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Tag\Update\UpdateInputData;
use Song\Application\UseCase\Tag\Update\UpdateUseCaseInterface;

class UpdateSongTagController extends Controller
{
    public function __construct(
        private readonly UpdateUseCaseInterface $interactor,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(UpdateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($inputData));
    }
}
