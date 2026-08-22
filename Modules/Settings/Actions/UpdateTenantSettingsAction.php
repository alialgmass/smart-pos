<?php

namespace Modules\Settings\Actions;

use Modules\Tenancy\Models\Tenant;

class UpdateTenantSettingsAction
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function execute(Tenant $tenant, array $settings): Tenant
    {
        $current = $tenant->settings ?? [];

        $merged = array_merge($current, $settings);

        $tenant->forceFill([
            'settings' => $merged,
        ])->save();

        return $tenant;
    }
}
