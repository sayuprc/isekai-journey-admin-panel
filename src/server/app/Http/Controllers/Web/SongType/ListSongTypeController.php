<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\SongType\SongTypeListPresenter;
use Illuminate\Contracts\View\View;
use SongType\Application\UseCase\List\ListUseCaseInterface;

class ListSongTypeController extends Controller
{
    public function index(ListUseCaseInterface $interactor, SongTypeListPresenter $presenter): View
    {
        $heads = [
            '名前',
            '表示順',
            '',
        ];

        $songTypes = $presenter->present($interactor->handle());

        return view('songTypes.list.index', compact('heads', 'songTypes'));
    }
}
