<?php

use App\Http\Controllers\Api\V1\Pharmacy\PharmacyInventoryController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyLicenseController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyPrescriptionOrderController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'role:Pharmacy,api'])
    ->group(function () {
        Route::post('/pharmacies', [PharmacyRegistrationController::class, 'store'])
            ->name('pharmacies.store');
        Route::post('/pharmacies/licenses', [PharmacyLicenseController::class, 'store'])
            ->name('pharmacies.licenses.store');
        Route::get('/pharmacies/inventory', [PharmacyInventoryController::class, 'index'])
            ->name('pharmacies.inventory.index');
        Route::post('/pharmacies/inventory', [PharmacyInventoryController::class, 'store'])
            ->name('pharmacies.inventory.store');
        Route::patch('/pharmacies/inventory/{inventoryItem}', [PharmacyInventoryController::class, 'update'])
            ->name('pharmacies.inventory.update');
        Route::get('/pharmacies/prescriptions', [PharmacyPrescriptionOrderController::class, 'index'])
            ->name('pharmacies.prescriptions.index');
        Route::patch('/pharmacies/prescriptions/{order}/process', [
            PharmacyPrescriptionOrderController::class,
            'process',
        ])->name('pharmacies.prescriptions.process');
        Route::patch('/pharmacies/prescriptions/{order}/dispense', [
            PharmacyPrescriptionOrderController::class,
            'dispense',
        ])->name('pharmacies.prescriptions.dispense');
    });
