<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Providers;

use App\Features\JourneyLog\Port\UseCases\Edit\EditRequest;
use App\Providers\RequestServiceProviderBase;
use App\Features\JourneyLog\Adapter\Web\Requests\EditRequest as WebRequest;

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
