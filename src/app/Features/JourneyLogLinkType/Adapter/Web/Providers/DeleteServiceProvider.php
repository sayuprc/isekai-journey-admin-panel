<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Providers;

use App\Features\JourneyLogLinkType\Adapter\Web\Requests\DeleteRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Delete\DeleteRequest;
use App\Providers\RequestServiceProviderBase;

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
