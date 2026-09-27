<?php

use App\Http\Controllers\Admin\Auth\PermissionController;
use App\Http\Controllers\Admin\Auth\RoleController;
use App\Http\Controllers\Admin\Auth\UserController;
use App\Http\Controllers\Admin\Menu\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'verified', 'role.redirect:Admin'])->group(function () {
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
});
