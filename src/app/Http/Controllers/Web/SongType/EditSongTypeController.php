<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\SongType\SongTypePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use SongType\UseCases\Edit\EditRequest;
use SongType\UseCases\Edit\EditUseCaseInterface;
use SongType\UseCases\Get\GetRequest;
use SongType\UseCases\Get\GetUseCaseInterface;
use Support\Route\RouteMap;

class EditSongTypeController extends Controller
{
    public function index(
        string $songTypeId,
        GetUseCaseInterface $getInteractor,
        SongTypePresenter $presenter,
    ): RedirectResponse|View {
        $result = $getInteractor->handle(new GetRequest($songTypeId));

        if ($result->isErr()) {
            return redirect()
                ->route(RouteMap::ListSongTypes)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $songType = $presenter->present($result->unwrap());

        return view('songTypes.edit.index', compact('songType'));
    }

    public function handle(EditRequest $request, EditUseCaseInterface $interactor): RedirectResponse
    {
        $result = $interactor->handle($request);

        return $result->isErr()
            ? back()
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ])
                ->withInput()
            : redirect()
                ->route(RouteMap::ListSongTypes)
                ->with([
                    'message' => '更新しました',
                ]);
    }
}
