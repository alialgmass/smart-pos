<?php

namespace Modules\Settings\DTOs;

use Illuminate\Support\Carbon;

class TenantSettingsData
{
    /**
     * @param  array<string, mixed>  $invoice
     * @param  array<string, mixed>  $tax
     * @param  array<string, mixed>  $printer
     */
    public function __construct(
        public readonly array $invoice = [],
        public readonly array $tax = [],
        public readonly array $printer = [],
        public readonly ?Carbon $updatedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            invoice: $data['invoice'] ?? [],
            tax: $data['tax'] ?? [],
            printer: $data['printer'] ?? [],
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'invoice' => $this->invoice,
            'tax' => $this->tax,
            'printer' => $this->printer,
        ];
    }
}
