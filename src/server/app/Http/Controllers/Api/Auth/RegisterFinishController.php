<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\RegisterFinishPresenter;
use Auth\Application\UseCase\RegisterFinish\RegisterFinishInputData;
use Auth\Application\UseCase\RegisterFinish\RegisterFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterFinishController extends Controller
{
    public function __construct(
        private readonly RegisterFinishUseCase $useCase,
        private readonly RegisterFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $credential */
        $credential = $request->input('credential', []);

        return $this->presenter->present(
            $this->useCase->handle(
                new RegisterFinishInputData(
                    $request->string('authCeremonyId')->toString(),
                    $credential,
                ),
            ),
        );
    }
}
