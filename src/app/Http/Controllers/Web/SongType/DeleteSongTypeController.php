<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SongType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use SongType\Route\SongTypeRouteMap;
use SongType\UseCases\Delete\DeleteInputData;
use SongType\UseCases\Delete\DeleteUseCaseInterface;

class DeleteSongTypeController extends Controller
{
    public function handle(DeleteInputData $inputData, DeleteUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($inputData);
        } catch (Exception $e) {
            return back()->withErrors([
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route(SongTypeRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
