<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\JourneyLogLinkType\JourneyLogLinkTypePresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use JourneyLogLinkType\UseCases\Edit\EditInputData;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use JourneyLogLinkType\UseCases\Get\GetInputData;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;

class EditJourneyLogLinkTypeController extends Controller
{
    public function index(string $journeyLogLinkTypeId, GetUseCaseInterface $interactor, JourneyLogLinkTypePresenter $presenter): RedirectResponse|View
    {
        $result = $interactor->handle(new GetInputData($journeyLogLinkTypeId));

        if ($result->isErr()) {
            return redirect()
                ->route(JourneyLogLinkTypeRouteMap::List)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $journeyLogLinkType = $presenter->present($result->unwrap());

        return view('journeyLogLinkTypes.edit.index', compact('journeyLogLinkType'));
    }

    public function handle(EditInputData $inputData, EditUseCaseInterface $interactor): RedirectResponse
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
                'message' => '更新しました',
            ]);
    }
}
