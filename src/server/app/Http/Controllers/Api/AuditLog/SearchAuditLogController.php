<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\AuditLog\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Support\UseCase\AuditLog\Search\SearchInputData;
use Support\UseCase\AuditLog\Search\SearchUseCase;

class SearchAuditLogController extends Controller
{
    public function __construct(
        private readonly SearchUseCase $useCase,
        private readonly SearchPresenter $presenter,
    ) {
    }

    public function handle(SearchInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
