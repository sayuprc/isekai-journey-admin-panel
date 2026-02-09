<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;

class DeletePresenter
{
    /**
     * @param Result<null, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            fn () => response()->json(status: 204),
            fn (string $message) => response()->json(
                new ErrorResponse()->setMessage($message),
                400,
            ),
        );
    }
}
