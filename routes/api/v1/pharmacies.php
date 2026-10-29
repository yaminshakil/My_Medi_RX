<?php

use App\Http\Controllers\Api\V1\Pharmacy\PharmacyInventoryController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyLicenseController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyPrescriptionOrderController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')
    ->group(function () {
        Route::post('/pharmacies', [PharmacyRegistrationController::class, 'store'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.store');
        Route::post('/pharmacies/licenses', [PharmacyLicenseController::class, 'store'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.licenses.store');
        Route::get('/pharmacies/inventory', [PharmacyInventoryController::class, 'index'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.inventory.index');
        Route::post('/pharmacies/inventory', [PharmacyInventoryController::class, 'store'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.inventory.store');
        Route::patch('/pharmacies/inventory/{inventoryItem}', [PharmacyInventoryController::class, 'update'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.inventory.update');
        Route::get('/pharmacies/prescriptions', [PharmacyPrescriptionOrderController::class, 'index'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.prescriptions.index');
        Route::patch('/pharmacies/prescriptions/{order}/process', [
            PharmacyPrescriptionOrderController::class,
            'process',
        ])->middleware('role:Pharmacy,api')
            ->name('pharmacies.prescriptions.process');
        Route::patch('/pharmacies/prescriptions/{order}/dispense', [
            PharmacyPrescriptionOrderController::class,
            'dispense',
        ])->middleware('role:Pharmacy,api')
            ->name('pharmacies.prescriptions.dispense');
    });
