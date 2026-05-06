<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\UseCase\RegisterStart\RegisterStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PasskeyRegistrationStartResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class RegisterStartPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<RegisterStartOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (RegisterStartOutputData $output) => [
                new PasskeyRegistrationStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object) $output->publicKey),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
