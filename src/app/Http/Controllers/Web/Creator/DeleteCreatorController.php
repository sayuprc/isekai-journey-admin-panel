<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\Route\CreatorRouteMap;
use Creator\UseCases\Delete\DeleteRequest;
use Creator\UseCases\Delete\DeleteUseCaseInterface;
use Exception;
use Illuminate\Http\RedirectResponse;

class DeleteCreatorController extends Controller
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
            ->route(CreatorRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
