<?php

namespace Modules\Tenancy\DTOs;

final readonly class RegisterTenantData
{
    public function __construct(
        public string $storeName,
        public string $ownerName,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            storeName: $input['store_name'],
            ownerName: $input['owner_name'],
            email: $input['email'],
            password: $input['password'],
        );
    }
}
