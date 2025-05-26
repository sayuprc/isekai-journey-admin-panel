<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use JourneyLogLinkType\UseCases\Delete\DeleteInputData;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;

class DeleteJourneyLogLinkTypeController extends Controller
{
    public function handle(DeleteInputData $inputData, DeleteUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($inputData);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(JourneyLogLinkTypeRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
