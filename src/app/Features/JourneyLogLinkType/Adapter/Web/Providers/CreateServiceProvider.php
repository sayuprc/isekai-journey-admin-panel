<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Providers;

use App\Features\JourneyLogLinkType\Adapter\Web\Requests\CreateRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Create\CreateRequest;
use App\Providers\RequestServiceProviderBase;

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
