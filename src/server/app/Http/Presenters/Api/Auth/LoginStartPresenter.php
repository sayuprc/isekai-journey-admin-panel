<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\UseCase\LoginStart\LoginStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PasskeyLoginStartResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class LoginStartPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<LoginStartOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (LoginStartOutputData $output) => [
                new PasskeyLoginStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object)$output->publicKey),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
