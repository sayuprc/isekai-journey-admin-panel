<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\CreatePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;

class CreateSongTypeController extends Controller
{
    public function __construct(
        private readonly CreateUseCaseInterface $interactor,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(CreateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($inputData));
    }
}
