<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\ListPresenter;
use Illuminate\Http\JsonResponse;
use SongTag\Application\UseCase\List\ListUseCaseInterface;

class ListSongTagController extends Controller
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
