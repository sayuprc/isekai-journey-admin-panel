<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Providers;

use App\Providers\Request\RequestServiceProviderBase;
use JourneyLog\Adapter\Web\Requests\CreateRequest as WebRequest;
use JourneyLog\Port\UseCases\Create\CreateRequest;

class CreateServiceProvider extends RequestServiceProviderBase
{
    public function register(): void
    {
        $this->app->bind(CreateRequest::class, function (): CreateRequest {
            $request = $this->app->make(WebRequest::class);
            assert($request instanceof WebRequest);

            return $this->getMapper()->mapFromArray(CreateRequest::class, $request->validated());
        });
    }
}
