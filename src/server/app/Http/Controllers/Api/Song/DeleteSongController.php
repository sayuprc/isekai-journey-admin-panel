<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Song\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCase;

class DeleteSongController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $songId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new DeleteInputData($songId)));
    }
}
