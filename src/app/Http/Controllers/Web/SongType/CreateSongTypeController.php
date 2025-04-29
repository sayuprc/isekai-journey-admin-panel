<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use SongType\UseCases\Create\CreateRequest;
use SongType\UseCases\Create\CreateUseCaseInterface;
use Support\Route\RouteMap;

class CreateSongTypeController extends Controller
{
    public function index(): View
    {
        return view('songTypes.create.index');
    }

    public function handle(CreateRequest $request, CreateUseCaseInterface $interactor): RedirectResponse
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
                ->with(['message' => '登録完了しました']);
    }
}
