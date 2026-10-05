<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Hospital\HospitalController;
use App\Http\Controllers\Frontend\Diagnostic\DiagnosticController;

Route::middleware('web')->group(function () {
    Route::get('/diagnostics', [DiagnosticController::class, 'index'])->name('diagnostics.index');
    Route::get('/diagnostics/{hospital}', [DiagnosticController::class, 'show'])->name('diagnostics.show');
});
Route::prefix('admin')->middleware(['auth', 'verified', 'role.redirect:Admin'])->group(function () {
    /* Hospital CRUD */
    Route::get('/hospitals', [HospitalController::class,'index'])->name('hospital.index');
    Route::get('/hospitals/create', [HospitalController::class,'create'])->name('hospital.create');
    Route::post('/hospitals/create', [HospitalController::class,'store']);
    Route::get('/hospitals/{id}/edit', [HospitalController::class,'edit'])->name('hospital.edit');
    Route::post('/hospitals/{id}', [HospitalController::class,'update'])->name('hospital.update');
    Route::delete('/hospitals/{id}/delete', [HospitalController::class,'destroy'])->name('hospital.delete');

    Route::get('chamberschedule/{hospital_uuid}/doctor/{doctor_uuid}', [DiagnosticController::class, 'getChamberSchedule'])->name('updatechamber.schedule');
    Route::post('chamberschedule/{id}', [DiagnosticController::class, 'updateChamberSchedule'])->name('chamberschedule.update');
    Route::post('addservices/', [DiagnosticController::class, 'addDiagnosticServices'])->name('diagnostic.addservies');
    Route::post('services/update/{id}', [DiagnosticController::class, 'updateDiagnosticServices'])->name('diagnostic.servies.update');
    Route::delete('services/delete/{id}', [DiagnosticController::class, 'deleteDiagnosticServices'])->name('diagnostic.servies.destroy');
    Route::post('/diagnostics/{id}/rate', [DiagnosticController::class, 'storeRating'])->name('diagnostics.rate');
});
