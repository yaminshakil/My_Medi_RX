<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SystemSettngsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');
});

Route::prefix('admin')->middleware('auth', 'role:Admin')->group(function () {
    Route::prefix('settings/system')->name('settings.system.')->group(function () {
        Route::get('/', [SystemSettngsController::class, 'index'])->name('index');
        Route::get('/general/edit', [SystemSettngsController::class, 'editGeneralSettings'])->name('general.edit');
        Route::post('/general/update', [SystemSettngsController::class, 'updateGeneralSettings'])->name('general.update');
        Route::get('/site-identity/edit', [SystemSettngsController::class, 'editSiteIdentitySettings'])->name('site_identity.edit');
        Route::post('/site-identity/update', [SystemSettngsController::class, 'updateSiteIdentitySettings'])->name('site_identity.update');
        Route::get('/color/edit', [SystemSettngsController::class, 'editColorSettings'])->name('color.edit');
        Route::post('/color/update', [SystemSettngsController::class, 'updateColorSettings'])->name('color.update');
        Route::get('/seo/edit', [SystemSettngsController::class, 'editSeoSettings'])->name('seo.edit');
        Route::post('/seo/update', [SystemSettngsController::class, 'updateSeoSettings'])->name('seo.update');
        Route::get('/systemconfig', [SystemSettngsController::class, 'systemConfiguration'])->name('systemconfig.index');
        Route::get('/cachesettings', [SystemSettngsController::class, 'cacheSettings'])->name('cachesettings.index');
        Route::get('/routecachesettings', [SystemSettngsController::class, 'clearRouteCachSettings'])->name('routecachesettings.clear');
        Route::get('/viewcachesettings', [SystemSettngsController::class, 'clearViewCachSettings'])->name('viewcachesettings.clear');
        Route::get('/configcachesettings', [SystemSettngsController::class, 'clearConfigCachSettings'])->name('configcachesettings.clear');
        Route::get('/eventcachesettings', [SystemSettngsController::class, 'clearEventCachSettings'])->name('eventcachesettings.clear');
        Route::get('/allcachesettings', [SystemSettngsController::class, 'clearAllCachSettings'])->name('allcachesettings.clear');
    });
});
