<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\Creator\CreatorListPresenter;
use App\Http\Presenters\Web\SongType\SongTypeListPresenter;
use Creator\UseCases\List\ListUseCaseInterface as CreatorListUseCaseInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Mockery\Exception;
use Song\Route\SongRouteMap;
use Song\UseCases\Create\CreateInputData;
use Song\UseCases\Create\CreateUseCaseInterface;
use SongType\UseCases\List\ListUseCaseInterface as SongTypeListUseCaseInterface;

class CreateSongController extends Controller
{
    public function index(
        CreatorListUseCaseInterface $creatorListInteractor,
        CreatorListPresenter $creatorPresenter,
        SongTypeListUseCaseInterface $songTypeListInteractor,
        SongTypeListPresenter $songTypePresenter,
    ): View {
        $creators = $creatorPresenter->present($creatorListInteractor->handle());
        $songTypes = $songTypePresenter->present($songTypeListInteractor->handle());

        return view('songs.create.index', compact('creators', 'songTypes'));
    }

    public function handle(CreateInputData $inputData, CreateUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($inputData);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(SongRouteMap::List)
            ->with(['message' => '登録完了しました']);
    }
}
