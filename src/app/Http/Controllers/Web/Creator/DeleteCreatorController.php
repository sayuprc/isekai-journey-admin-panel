<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\UseCases\Delete\DeleteRequest;
use Creator\UseCases\Delete\DeleteUseCaseInterface;
use Exception;
use Illuminate\Http\RedirectResponse;
use Support\Route\RouteMap;

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
            ->route(RouteMap::ListCreators)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
