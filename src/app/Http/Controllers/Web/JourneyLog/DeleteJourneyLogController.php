<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLog;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Route\JourneyLogRouteMap;
use JourneyLog\UseCases\Delete\DeleteInputData;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;

class DeleteJourneyLogController extends Controller
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
            ->route(JourneyLogRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
