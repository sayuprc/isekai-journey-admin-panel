<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\ListPresenter;
use Illuminate\Http\JsonResponse;
use SongType\Application\UseCase\List\ListUseCaseInterface;

class ListSongTypeController extends Controller
{
    public function __construct(
        private readonly ListUseCaseInterface $interactor,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle());
    }
}
