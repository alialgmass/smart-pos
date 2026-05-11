<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Actions\UpdateTenantSettingsAction;
use Modules\Settings\DTOs\TenantSettingsData;

class PrinterSettingsController extends Controller
{
    public function edit(): Response
    {
        $tenant = auth()->user()->tenant;

        $settings = TenantSettingsData::fromArray($tenant->settings ?? []);

        return Inertia::render('Settings/Printer', [
            'settings' => $settings->printer,
        ]);
    }

    public function update(UpdateTenantSettingsAction $action): RedirectResponse
    {
        $data = request()->validate([
            'type' => ['nullable', 'string', 'in:thermal,a4,receipt'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'auto_cut' => ['boolean'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $tenant = auth()->user()->tenant;

        $action->execute($tenant, [
            'printer' => $data,
        ]);

        return redirect()->route('settings.printer.edit')->with('status', __('Printer settings updated.'));
    }
}
