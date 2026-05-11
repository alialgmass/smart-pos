<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Actions\UpdateTenantSettingsAction;
use Modules\Settings\DTOs\TenantSettingsData;

class InvoiceSettingsController extends Controller
{
    public function edit(): Response
    {
        $tenant = auth()->user()->tenant;

        $settings = TenantSettingsData::fromArray($tenant->settings ?? []);

        return Inertia::render('Settings/Invoice', [
            'settings' => $settings->invoice,
        ]);
    }

    public function update(UpdateTenantSettingsAction $action): RedirectResponse
    {
        $data = request()->validate([
            'prefix' => ['nullable', 'string', 'max:20'],
            'format' => ['nullable', 'string', 'max:100'],
            'show_logo' => ['boolean'],
            'show_address' => ['boolean'],
            'footer_text' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant = auth()->user()->tenant;

        $action->execute($tenant, [
            'invoice' => $data,
        ]);

        return redirect()->route('settings.invoice.edit')->with('status', __('Invoice settings updated.'));
    }
}
