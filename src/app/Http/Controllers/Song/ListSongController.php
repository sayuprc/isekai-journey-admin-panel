<?php

declare(strict_types=1);

namespace App\Http\Controllers\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Song\ListViewSong;
use Illuminate\Contracts\View\View;
use Song\UseCases\List\ListUseCaseInterface;

class ListSongController extends Controller
{
    public function index(ListUseCaseInterface $interactor): View
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
