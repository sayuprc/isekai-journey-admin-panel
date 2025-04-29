<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use SongType\UseCases\Delete\DeleteRequest;
use SongType\UseCases\Delete\DeleteUseCaseInterface;
use Support\Route\RouteMap;

class DeleteSongTypeController extends Controller
{
    public function handle(DeleteRequest $request, DeleteUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($request);
        } catch (Exception $e) {
            return back()->withErrors([
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route(RouteMap::ListSongTypes)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
