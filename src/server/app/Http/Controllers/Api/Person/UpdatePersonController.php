<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Person\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\UseCase\Update\UpdateInputData;
use Person\Application\UseCase\Update\UpdateUseCase;

class UpdatePersonController extends Controller
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(UpdateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
