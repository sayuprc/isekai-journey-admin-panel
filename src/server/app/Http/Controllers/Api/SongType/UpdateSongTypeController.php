<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;

class UpdateSongTypeController extends Controller
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
