<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongAttribute;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongAttribute\ListPresenter;
use Illuminate\Http\JsonResponse;
use SongAttribute\Application\UseCase\List\ListUseCaseInterface;

class ListSongAttributeController extends Controller
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
