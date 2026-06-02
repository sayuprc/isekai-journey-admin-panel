<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Viewer\V1\SiteStats;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Viewer\V1\SiteStats\GetPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Viewer\UseCase\Count\CountUseCase;

class GetSiteStatsController extends Controller
{
    public function __construct(
        private readonly CountUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle());
    }
}
