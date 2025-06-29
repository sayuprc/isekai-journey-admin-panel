<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use Illuminate\Http\JsonResponse;

class SongTypeDeletePresenter
{
    public function present(): JsonResponse
    {
        return response()->json(status: 204);
    }
}
