<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\Creator\CreatorPresenter;
use Creator\Route\CreatorRouteMap;
use Creator\UseCases\Edit\EditInputData;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Creator\UseCases\Get\GetInputData;
use Creator\UseCases\Get\GetUseCaseInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EditCreatorController extends Controller
{
    public function index(
        string $creatorId,
        GetUseCaseInterface $getInteractor,
        CreatorPresenter $presenter,
    ): RedirectResponse|View {
        $result = $getInteractor->handle(new GetInputData($creatorId));

        if ($result->isErr()) {
            return redirect()
                ->route(CreatorRouteMap::List)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $creator = $presenter->present($result->unwrap());

        return view('creators.edit.index', compact('creator'));
    }

    public function handle(EditInputData $inputData, EditUseCaseInterface $interactor): RedirectResponse
    {
        $result = $interactor->handle($inputData);

        return $result->isErr()
            ? back()
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ])
                ->withInput()
            : redirect()
                ->route(CreatorRouteMap::List)
                ->with([
                    'message' => '更新しました',
                ]);
    }
}
