<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Requests\CreateRequest as WebCreateRequest;
use App\Features\JourneyLog\Port\UseCases\Create\CreateInteractor;
use App\Features\JourneyLog\Port\UseCases\Create\CreateRequest;
use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
use App\Features\JourneyLogLinkType\Port\UseCases\List\ListInteractor;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateJourneyLogController extends Controller
{
    public function index(ListInteractor $interactor): View
    {
        $response = $interactor->handle();

        $journeyLogLinkTypes = [];

        foreach ($response->journeyLogLinkTypes as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = new ListViewJourneyLogLinkType($journeyLogLinkType);
        }

        return view('journeyLogs.create.index', compact('journeyLogLinkTypes'));
    }

    public function handle(WebCreateRequest $request, CreateInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new CreateRequest(
                $validated['story'],
                $validated['from_on'],
                $validated['to_on'],
                (int)$validated['order_no'],
                array_map(function (array $item): array {
                    return [
                        'journey_log_link_name' => $item['journey_log_link_name'],
                        'url' => $item['url'],
                        'order_no' => (int)$item['order_no'],
                        'journey_log_link_type_id' => $item['journey_log_link_type_id'],
                    ];
                }, $validated['journey_log_links'] ?? []),
            ));
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('journey-logs.index')
            ->with(['message' => '登録完了しました']);
    }
}
