<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use SensitiveParameter;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use User\Application\UseCase\Login\LoginInputData;

class LoginRequest implements Request
{
    use IsRequest;

    public function __construct(
        public string $email,
        #[SensitiveParameter] public string $password
    ) {
    }

    public function toInputData(): LoginInputData
    {
        return new LoginInputData($this->email, $this->password);
    }
}
