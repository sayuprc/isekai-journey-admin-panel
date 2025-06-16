<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Route\CreatorRouteMap;
use Exception;
use Illuminate\Http\RedirectResponse;

class DeleteCreatorController extends Controller
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
            ->route(CreatorRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
