<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\AdminUser;

use AdminUser\Application\UseCase\List\ListOutputData;
use AdminUser\Domain\Models\AdminUser;
use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\AdminUserListResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListOutputData $outputData) => [
                new AdminUserListResponse()->setAdminUsers(
                    array_map(
                        fn (AdminUser $adminUser) => $this->converter->toOpenApiAdminUser($adminUser),
                        $outputData->adminUsers,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
