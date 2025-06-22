<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeListPresenter;
use OpenAPI\Client\Model\ListSongTypeResponse;
use SongType\Application\UseCase\List\ListUseCaseInterface;

class ListSongTypeController extends Controller
{
    public function handle(ListUseCaseInterface $interactor, SongTypeListPresenter $presenter): ListSongTypeResponse
    {
        return $presenter->present($interactor->handle());
    }
}
