<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Override;

readonly class PasskeyCeremonyStore implements PasskeyCeremonyStoreInterface
{
    public function __construct(private CacheFactory $cache)
    {
    }

    #[Override]
    public function put(PasskeyCeremonyState $state): void
    {
        $ttl = config('auth.passkey.ceremony_ttl_seconds');

        $this->cache
            ->store(config()->string('auth.passkey.ceremony_cache_store'))
            ->put(
                $this->key($state->authCeremonyId),
                $state->toArray(),
                is_int($ttl) ? $ttl : 300,
            );
    }

    #[Override]
    public function pull(string $authCeremonyId): ?PasskeyCeremonyState
    {
        /** @var array<string, mixed>|null $payload */
        $payload = $this->cache
            ->store(config()->string('auth.passkey.ceremony_cache_store'))
            ->pull($this->key($authCeremonyId));

        if ($payload === null) {
            return null;
        }

        /** @var array{
         *   auth_ceremony_id: string,
         *   type: string,
         *   email: string,
         *   options_json: string,
         *   admin_user_id?: string|null,
         *   registration_token_id?: string|null
         * } $payload
         */
        return PasskeyCeremonyState::fromArray($payload);
    }

    private function key(string $authCeremonyId): string
    {
        return 'auth:passkey:ceremony:' . $authCeremonyId;
    }
}
