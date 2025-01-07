<?php

declare(strict_types=1);

namespace App\Http\Controllers\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Song\SongListPresenter;
use Illuminate\Contracts\View\View;
use Song\UseCases\List\ListUseCaseInterface;

class ListSongController extends Controller
{
    public function index(ListUseCaseInterface $interactor, SongListPresenter $presenter): View
    {
        $heads = [
            'タイトル',
            '説明',
            'リリース日',
            // '楽曲種別', // TODO 楽曲種別を実装したらここも実装する
            '表示順',
            '',
        ];

        $songs = $presenter->present($interactor->handle());

        return view('songs.list.index', compact('heads', 'songs'));
    }
}
