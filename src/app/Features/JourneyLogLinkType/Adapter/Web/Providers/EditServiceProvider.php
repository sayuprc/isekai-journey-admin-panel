<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Providers;

use App\Features\JourneyLogLinkType\Adapter\Web\Requests\EditRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Edit\EditRequest;
use App\Providers\RequestServiceProviderBase;

class EditServiceProvider extends RequestServiceProviderBase
{
    public function register(): void
    {
        $this->app->bind(EditRequest::class, function (): EditRequest {
            $request = $this->app->make(WebRequest::class);
            assert($request instanceof WebRequest);

            return $this->getMapper()->mapFromArray(EditRequest::class, $request->validated());
        });
    }
}
