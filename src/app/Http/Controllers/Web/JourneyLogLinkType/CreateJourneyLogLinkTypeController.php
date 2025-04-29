<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use JourneyLogLinkType\UseCases\Create\CreateInputData;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;

class CreateJourneyLogLinkTypeController extends Controller
{
    public function index(): View
    {
        return view('journeyLogLinkTypes.create.index');
    }

    public function handle(CreateInputData $inputData, CreateUseCaseInterface $interactor): RedirectResponse
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
            ->with(['message' => '登録完了しました']);
    }
}
