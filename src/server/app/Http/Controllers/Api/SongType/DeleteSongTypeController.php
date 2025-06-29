<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\DeletePresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;

class DeleteSongTypeController extends Controller
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $songTypeId): JsonResponse
    {
        $this->interactor->handle(new DeleteInputData($songTypeId));

        return $this->presenter->present();
    }
}
