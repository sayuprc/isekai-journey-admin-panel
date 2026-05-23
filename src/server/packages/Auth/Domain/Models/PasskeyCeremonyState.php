<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

readonly class PasskeyCeremonyState
{
    public function __construct(
        public string $authCeremonyId,
        public string $type,
        public ?string $token,
        public string $email,
        public ?string $name,
        public string $adminUserId,
        public string $optionsJson,
    ) {
    }

    /**
     * @return array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   token: string|null,
     *   email: string,
     *   name: string|null,
     *   admin_user_id: string,
     *   options_json: string
     * }
     */
    public function toArray(): array
    {
        return [
            'auth_ceremony_id' => $this->authCeremonyId,
            'type' => $this->type,
            'token' => $this->token,
            'email' => $this->email,
            'admin_user_id' => $this->adminUserId,
            'name' => $this->name,
            'options_json' => $this->optionsJson,
        ];
    }

    /**
     * @param array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   token: string|null,
     *   email: string,
     *   name: string|null,
     *   admin_user_id: string,
     *   options_json: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['auth_ceremony_id'],
            $data['type'],
            $data['token'],
            $data['email'],
            $data['name'],
            $data['admin_user_id'],
            $data['options_json'],
        );
    }
}
