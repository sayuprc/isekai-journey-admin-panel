<?php

declare(strict_types=1);

namespace Tests;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Closure;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getJourneyLogRepository(
        ?Closure $listJourneyLogs = null,
        ?Closure $getJourneyLog = null,
        ?Closure $createJourneyLog = null,
        ?Closure $editJourneyLog = null,
        ?Closure $deleteJourneyLog = null,
    ): JourneyLogRepositoryInterface {
        $none = function () {};

        return new class (
            $listJourneyLogs ?? $none,
            $getJourneyLog ?? $none,
            $createJourneyLog ?? $none,
            $editJourneyLog ?? $none,
            $deleteJourneyLog ?? $none,
        ) implements JourneyLogRepositoryInterface {
            public function __construct(
                private Closure $listJourneyLogs,
                private Closure $getJourneyLog,
                private Closure $createJourneyLog,
                private Closure $editJourneyLog,
                private Closure $deleteJourneyLog,
            ) {
            }

            public function listJourneyLogs(): array
            {
                return ($this->listJourneyLogs)();
            }

            public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
            {
                return ($this->getJourneyLog)($journeyLogId);
            }

            public function createJourneyLog(JourneyLog $journeyLog): void
            {
                ($this->createJourneyLog)($journeyLog);
            }

            public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
            {
                return ($this->editJourneyLog)($journeyLog);
            }

            public function deleteJourneyLog(JourneyLogId $journeyLogId): void
            {
                ($this->deleteJourneyLog)($journeyLogId);
            }
        };
    }

    protected function getJourneyLogLinkTypeRepository(
        ?Closure $listJourneyLogLinkTypes = null,
        ?Closure $createJourneyLogLinkType = null,
    ): JourneyLogLinkTypeRepositoryInterface {
        $none = function () {};

        return new class (
            $listJourneyLogLinkTypes ?? $none,
            $createJourneyLogLinkType ?? $none,
        ) implements JourneyLogLinkTypeRepositoryInterface {
            public function __construct(
                private readonly Closure $listJourneyLogLinkTypes,
                private readonly Closure $createJourneyLogLinkType,
            ) {
            }

            public function listJourneyLogLinkTypes(): array
            {
                return ($this->listJourneyLogLinkTypes)();
            }

            public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
            {
                ($this->createJourneyLogLinkType)($journeyLogLinkType);
            }
        };
    }
}
