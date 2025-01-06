<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Providers;

use App\Providers\Request\RequestServiceProviderBase;
use JourneyLog\Adapter\Web\Requests\EditRequest as WebRequest;
use JourneyLog\Port\UseCases\Edit\EditRequest;

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
