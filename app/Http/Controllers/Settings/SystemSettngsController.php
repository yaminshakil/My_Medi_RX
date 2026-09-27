<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateColorSettingRequest;
use App\Http\Requests\Settings\UpdateGeneralSettingRequest;
use App\Http\Requests\Settings\UpdateSeoSettingRequest;
use App\Http\Requests\Settings\UpdateSiteIdentitySettingRequest;
use App\Models\Country;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class SystemSettngsController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('settings/SystemSettings/Index', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function editGeneralSettings()
    {
        $settings = Setting::pluck('meta_value', 'meta_key')->toArray();
        $countries = Country::all();

        return Inertia::render('settings/SystemSettings/General/Edit', [
            'settings'  => $settings,
            'countries' => $countries,
        ]);
    }

    public function updateGeneralSettings(UpdateGeneralSettingRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    // Logo and Icon

    public function editSiteIdentitySettings()
    {
        $settings = Setting::whereIn('meta_key', [
            'logo_path',
            'navbar_logo_path',
            'dashboard_logo_path',
            'favicon_path',
        ])->pluck('meta_value', 'meta_key')->toArray();

        return Inertia::render('settings/SystemSettings/SiteIdentity/Index', [
            'settings' => $settings,
        ]);
    }

    public function updateSiteIdentitySettings(UpdateSiteIdentitySettingRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function editColorSettings()
    {
        return Inertia::render('settings/SystemSettings/Color/Edit');
    }

    public function updateColorSettings(UpdateColorSettingRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function editSeoSettings()
    {
        $settings = Setting::pluck('meta_value', 'meta_key')->toArray();

        return Inertia::render('settings/SystemSettings/SeoSettings/SeoSettings', [
            'settings' => $settings,
        ]);
    }

    public function updateSeoSettings(UpdateSeoSettingRequest $request)
    {
        $data = $request->validated();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['meta_key' => $key], ['meta_value' => $value]);
        }

        return redirect()->back()->with('success', 'SEO settings updated.');
    }

    public function systemConfiguration()
    {
        return Inertia::render('settings/SystemSettings/SystemConfig/Index');
    }

    public function cacheSettings()
    {
        return Inertia::render('settings/SystemSettings/SystemConfig/CacheSettings/Index');
    }

    public function clearRouteCachSettings()
    {
        Artisan::call('route:clear');
        Artisan::call('route:cache');

        return redirect()->back()->with('success', 'Roter Cache updated.');
    }

    public function clearViewCachSettings()
    {
        Artisan::call('view:clear');
        Artisan::call('view:cache');

        return redirect()->back()->with('success', 'View Cache updated.');
    }

    public function clearConfigCachSettings()
    {
        Artisan::call('config:clear');
        Artisan::call('config:cache');

        return redirect()->back()->with('success', 'Config Cache updated.');
    }

    public function clearEventCachSettings()
    {
        Artisan::call('event:clear');
        Artisan::call('event:cache');

        return redirect()->back()->with('success', 'Event Cache updated.');
    }

    public function clearAllCachSettings()
    {
        Artisan::call('cache:clear');
        Artisan::call('optimize:clear');

        return redirect()->back()->with('success', 'All Cache updated.');
    }
}
