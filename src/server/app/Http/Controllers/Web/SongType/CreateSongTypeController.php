<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;
use SongType\Route\SongTypeRouteMap;

class CreateSongTypeController extends Controller
{
    public function index(): View
    {
        return view('songTypes.create.index');
    }

    public function handle(CreateInputData $inputData, CreateUseCaseInterface $interactor): RedirectResponse
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
                ->with(['message' => '登録完了しました']);
    }
}
