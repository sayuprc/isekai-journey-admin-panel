<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Support\Route\RouteMap;

class CreateCreatorController extends Controller
{
    public function index(): View
    {
        return view('creators.create.index');
    }

    public function handle(CreateRequest $request, CreateUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($request);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            // TODO クリエイター一覧ができたらそちらに移動
            ->route(RouteMap::ListJourneyLogs)
            ->with(['message' => '登録完了しました']);
    }
}
