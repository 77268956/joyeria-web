<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Traits\BuildsActivityChanges;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    use BuildsActivityChanges;

    /**
     * Display the system settings for the current store.
     */
    public function index(Request $request): View
    {
        $settings = $this->settingsFor($request);
        $settings->load('store');

        return view('admin.settings.index', [
            'settings' => $settings,
            'store' => $settings->store,
        ]);
    }

    /**
     * Persist the system settings for the current store.
     */
    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $settings = $this->settingsFor($request);
        $snapshot = $this->activitySnapshot($settings);
        $settings->update($request->validated());

        activity('configuracion')
            ->performedOn($settings)
            ->causedBy($request->user())
            ->event('updated')
            ->withChanges($this->activityUpdatedChanges($settings, $snapshot))
            ->log('Configuración del sistema actualizada');

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'La configuración del sistema se guardó correctamente.');
    }

    /**
     * Resolve the settings row for the current user's store.
     */
    private function settingsFor(Request $request): SystemSetting
    {
        $storeId = $request->user()?->store_id;

        if ($storeId !== null) {
            return SystemSetting::query()->firstOrCreate(['store_id' => $storeId]);
        }

        return SystemSetting::query()->orderBy('id')->firstOrFail();
    }
}
