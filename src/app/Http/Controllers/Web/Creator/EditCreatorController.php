<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\Creator\CreatorPresenter;
use Creator\UseCases\Edit\EditRequest;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Creator\UseCases\Get\GetRequest;
use Creator\UseCases\Get\GetUseCaseInterface;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Support\Route\RouteMap;

class EditCreatorController extends Controller
{
    public function index(
        string $creatorId,
        GetUseCaseInterface $getInteractor,
        CreatorPresenter $presenter,
    ): RedirectResponse|View {
        $result = $getInteractor->handle(new GetRequest($creatorId));

        if (! $result->isOk()) {
            return redirect()
                ->route(RouteMap::ListCreators)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $creator = $presenter->present($result->unwrap());

        return view('creators.edit.index', compact('creator'));
    }

    public function handle(EditRequest $request, EditUseCaseInterface $interactor): RedirectResponse
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
            ->route(RouteMap::ListCreators)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
