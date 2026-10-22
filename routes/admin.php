<?php

use App\Http\Controllers\Admin\Auth\PermissionController;
use App\Http\Controllers\Admin\Auth\RoleController;
use App\Http\Controllers\Admin\Auth\UserController;
use App\Http\Controllers\Admin\Menu\MenuController;
use App\Http\Controllers\Admin\Review\AdminReviewController;
use App\Http\Controllers\Admin\Vaccines\VaccineController;
use App\Http\Controllers\Admin\Pediatric\PediatricDoseRuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'verified', 'role.redirect:Admin,Doctor'])->group(function () {
    Route::post('/menus/update-order', [MenuController::class, 'updateOrder'])->name('menus.updateOrder');
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::post('/menus/create', [MenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{id}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::post('/menus/{id}/update', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{id}/destroy', [MenuController::class, 'destroy'])->name('menus.destroy');

    Route::resource('users', UserController::class)->middleware('permission:users.menu');

    Route::resource('roles', RoleController::class)->middleware('permission:roles.menu');

    Route::resource('permissions', PermissionController::class)->middleware('permission:permissions.menu');

    Route::post('/notifications/{notification}/read', function ($id) {
        $notification = auth()->user()
            ->unreadNotifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    })->name('notifications.read');
});

Route::middleware(['auth', 'role.redirect:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('reviews', AdminReviewController::class);
    Route::patch('reviews/{review}/toggle-approved', [AdminReviewController::class, 'toggleApproved'])
        ->name('reviews.toggle-approved');
});

Route::middleware(['auth', 'role.redirect:Admin,Doctor'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('vaccines/import', [VaccineController::class,'import',])->name('vaccines.import');
    Route::get('vaccines/template', [VaccineController::class,'template',])->name('vaccines.template');
    Route::resource('vaccines', VaccineController::class)
            ->only(['index', 'store', 'update', 'destroy']);
});

Route::middleware(['auth', 'role.redirect:Admin,Doctor'])
    ->prefix('admin/pediatric-dose-rules')
    ->name('admin.pediatric-dose-rules.')
    ->group(function () {
        Route::get('/', [PediatricDoseRuleController::class, 'index'])
            ->name('index');

        Route::get('/create', [PediatricDoseRuleController::class, 'create'])
            ->name('create');

        Route::post('/', [PediatricDoseRuleController::class, 'store'])
            ->name('store');

        Route::get('/{pediatricDoseRule}', [PediatricDoseRuleController::class, 'show'])
            ->name('show');

        Route::get('/{pediatricDoseRule}/edit', [PediatricDoseRuleController::class, 'edit'])
            ->name('edit');

        Route::put('/{pediatricDoseRule}', [PediatricDoseRuleController::class, 'update'])
            ->name('update');

        Route::post('/{pediatricDoseRule}/submit', [PediatricDoseRuleController::class, 'submit'])
            ->name('submit');

        Route::post('/{pediatricDoseRule}/approve', [PediatricDoseRuleController::class, 'approve'])
            ->name('approve');

        Route::post('/{pediatricDoseRule}/reject', [PediatricDoseRuleController::class, 'reject'])
            ->name('reject');

        Route::post('/{pediatricDoseRule}/activate', [PediatricDoseRuleController::class, 'activate'])
            ->name('activate');

        Route::post('/{pediatricDoseRule}/deactivate', [PediatricDoseRuleController::class, 'deactivate'])
            ->name('deactivate');
    });
