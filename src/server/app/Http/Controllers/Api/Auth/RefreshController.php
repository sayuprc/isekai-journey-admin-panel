<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\RefreshPresenter;
use Auth\Application\UseCase\Refresh\RefreshInputData;
use Auth\Application\UseCase\Refresh\RefreshUseCase;
use Illuminate\Http\JsonResponse;

class RefreshController extends Controller
{
    public function __construct(
        private readonly RefreshUseCase $useCase,
        private readonly RefreshPresenter $presenter,
    ) {
    }

    public function handle(RefreshInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
