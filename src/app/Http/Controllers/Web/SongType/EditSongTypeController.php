<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\SongType\SongTypePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use SongType\Route\SongTypeRouteMap;
use SongType\UseCases\Edit\EditInputData;
use SongType\UseCases\Edit\EditUseCaseInterface;
use SongType\UseCases\Get\GetInputData;
use SongType\UseCases\Get\GetUseCaseInterface;

class EditSongTypeController extends Controller
{
    public function index(
        string $songTypeId,
        GetUseCaseInterface $getInteractor,
        SongTypePresenter $presenter,
    ): RedirectResponse|View {
        $result = $getInteractor->handle(new GetInputData($songTypeId));

        if ($result->isErr()) {
            return redirect()
                ->route(SongTypeRouteMap::List)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $songType = $presenter->present($result->unwrap());

        return view('songTypes.edit.index', compact('songType'));
    }

    public function handle(EditInputData $inputData, EditUseCaseInterface $interactor): RedirectResponse
    {
        $result = $interactor->handle($inputData);

        return $result->isErr()
            ? back()
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ])
                ->withInput()
            : redirect()
                ->route(SongTypeRouteMap::List)
                ->with([
                    'message' => '更新しました',
                ]);
    }
}
