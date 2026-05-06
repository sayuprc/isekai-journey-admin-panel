<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

readonly class PasskeyCeremonyState
{
    public function __construct(
        public string $authCeremonyId,
        public string $type,
        public string $email,
        public string $optionsJson,
        public ?string $adminUserId = null,
        public ?string $registrationTokenId = null,
    ) {
    }

    /**
     * @return array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   email: string,
     *   options_json: string,
     *   admin_user_id: string|null,
     *   registration_token_id: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'auth_ceremony_id' => $this->authCeremonyId,
            'type' => $this->type,
            'email' => $this->email,
            'options_json' => $this->optionsJson,
            'admin_user_id' => $this->adminUserId,
            'registration_token_id' => $this->registrationTokenId,
        ];
    }

    /**
     * @param array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   email: string,
     *   options_json: string,
     *   admin_user_id?: string|null,
     *   registration_token_id?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['auth_ceremony_id'],
            $data['type'],
            $data['email'],
            $data['options_json'],
            $data['admin_user_id'] ?? null,
            $data['registration_token_id'] ?? null,
        );
    }
}
