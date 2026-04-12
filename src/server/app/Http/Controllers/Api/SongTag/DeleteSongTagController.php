<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Tag\Delete\DeleteInputData;
use Song\Application\UseCase\Tag\Delete\DeleteUseCaseInterface;

class DeleteSongTagController extends Controller
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(DeleteInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($inputData));
    }
}
