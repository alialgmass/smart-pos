<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Actions\UpdateTenantSettingsAction;
use Modules\Settings\DTOs\TenantSettingsData;

class TaxSettingsController extends Controller
{
    public function edit(): Response
    {
        $tenant = auth()->user()->tenant;

        $settings = TenantSettingsData::fromArray($tenant->settings ?? []);

        return Inertia::render('Settings/Tax', [
            'settings' => $settings->tax,
        ]);
    }

    public function update(UpdateTenantSettingsAction $action): RedirectResponse
    {
        $data = request()->validate([
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'apply_to' => ['nullable', 'string', 'in:all,food,beverage,merchandise'],
            'enabled' => ['boolean'],
        ]);

        $tenant = auth()->user()->tenant;

        $action->execute($tenant, [
            'tax' => $data,
        ]);

        return redirect()->route('settings.tax.edit')->with('status', __('Tax settings updated.'));
    }
}
