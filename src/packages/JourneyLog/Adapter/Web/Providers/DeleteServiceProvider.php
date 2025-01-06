<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Providers;

use App\Providers\Request\RequestServiceProviderBase;
use JourneyLog\Adapter\Web\Requests\DeleteRequest as WebRequest;
use JourneyLog\Port\UseCases\Delete\DeleteRequest;

class DeleteServiceProvider extends RequestServiceProviderBase
{
    public function register(): void
    {
        $this->app->bind(DeleteRequest::class, function (): DeleteRequest {
            $request = $this->app->make(WebRequest::class);
            assert($request instanceof WebRequest);

            return $this->getMapper()->mapFromArray(DeleteRequest::class, $request->validated());
        });
    }
}
