<?php

declare(strict_types=1);

namespace Song\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Song\Adapter\Web\Presenters\ListViewSong;
use Song\UseCases\List\ListInteractor;

class ListSongController extends Controller
{
    public function index(ListInteractor $interactor): View
    {
        $response = $interactor->handle();

        $songs = [];

        foreach ($response->songs as $song) {
            $songs[] = new ListViewSong($song);
        }

        $heads = [
            'タイトル',
            '説明',
            'リリース日',
            // '楽曲種別', // TODO 楽曲種別を実装したらここも実装する
            '表示順',
            '',
        ];

        return view('songs.list.index', compact('songs', 'heads'));
    }
}
