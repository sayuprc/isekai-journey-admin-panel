<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use App\Http\Responses\JsonResponse;

class DeletePresenter
{
    public function present(): JsonResponse
    {
        return new JsonResponse(status: 204);
    }
}
