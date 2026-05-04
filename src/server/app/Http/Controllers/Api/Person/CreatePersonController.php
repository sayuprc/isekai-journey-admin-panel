<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Person\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\UseCase\Create\CreateInputData;
use Person\Application\UseCase\Create\CreateUseCase;

class CreatePersonController extends Controller
{
    public function __construct(
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(CreateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
