<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AuditLog;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\AuditLogGetResponse;
use ResultType\Result;
use Support\UseCase\AuditLog\Get\GetOutputData;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (GetOutputData $outputData) => [
                new AuditLogGetResponse()->setAuditLog($this->converter->toOpenApiAuditLog($outputData->auditLog)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
