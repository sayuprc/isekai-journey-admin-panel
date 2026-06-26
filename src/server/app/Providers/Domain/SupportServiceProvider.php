<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Infrastructures\AuditLog\AuditLogRecorder;
use Support\Infrastructures\Clock;
use Support\Infrastructures\DbTransaction;
use Support\Infrastructures\Mapper;
use Support\Infrastructures\Query\AuditLog\EloquentAuditLogQueryService;
use Support\Infrastructures\Uuid\UuidConverter;
use Support\Infrastructures\Uuid\UuidGenerator;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;

class SupportServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(UuidConverterInterface::class, UuidConverter::class);
        $this->app->bind(TransactionInterface::class, DbTransaction::class);
        $this->app->bind(ClockInterface::class, Clock::class);
        $this->app->bind(AuditLogRecorderInterface::class, AuditLogRecorder::class);
        $this->app->bind(AuditLogQueryServiceInterface::class, EloquentAuditLogQueryService::class);
    }
}
