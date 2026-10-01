<?php

use App\Http\Controllers\Admin\Eprescriptions\InstructionController;
use App\Http\Controllers\Admin\Eprescriptions\ManufacturerController;
use App\Http\Controllers\Admin\Eprescriptions\MedicineController;
use App\Http\Controllers\Admin\Eprescriptions\MedicineDoseController;
use App\Http\Controllers\Admin\Eprescriptions\MedicineDurationController;
use App\Http\Controllers\Admin\Eprescriptions\PrescriptionController;
use App\Http\Controllers\Admin\Eprescriptions\VitalController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth', 'profile.exists', 'role.redirect:Admin,Doctor,Assistant,Patient'])->group(function () {
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('/eprescription/{uuid}/pdf', [PrescriptionController::class, 'downloadPdf'])
        ->name('eprescription.pdf');
});

Route::prefix('admin')->middleware(['web', 'auth', 'profile.exists', 'role.redirect:Admin,Doctor'])->group(function () {
    Route::get('/get-medicines', [PrescriptionController::class, 'getAllMedicine'])
        ->name('prescription.allmedicine');
    Route::get('eprescriptions/followup/{id}/appointment/{appointment_id}', [PrescriptionController::class, 'followup'])->name('prescriptions.followup');
    Route::get('eprescriptions/create', [PrescriptionController::class, 'saveAndNewPrescription'])->name('prescriptions.saveandnew');
    Route::post('eprescriptions/saveandnew/create', [PrescriptionController::class, 'saveAndNewPrescriptionCreate'])->name('prescriptions.saveandnew.store');
    Route::post('eprescriptions/appointment/create/{patient_id}', [PrescriptionController::class, 'quickAppointment'])->name('prescriptions.saveandnew.quickAppointment');
    Route::get('eprescriptions/get-patients', [PrescriptionController::class, 'getAllPateint'])
        ->name('prescriptions.patients');
    Route::post('eprescriptions/createpatient', [PrescriptionController::class, 'createPatient'])
        ->name('prescriptions.createpatient');

    // Eprescription
    Route::get('/api/instructions', [InstructionController::class, 'index'])->name('admin.instruction.index');
    Route::post('/api/instructions', [InstructionController::class, 'store'])->name('admin.instruction.store');
    Route::put('/api/instructions/{instruction}', [InstructionController::class, 'update'])->name('admin.instruction.update');
    Route::delete('/api/instructions/{instruction}', [InstructionController::class, 'destroy'])->name('admin.instruction.destroy');
    Route::post('/api/instructions/reorder', [InstructionController::class, 'reorder'])->name('admin.instruction.reorder');
});

Route::prefix('admin')->middleware(['web', 'auth', 'profile.exists', 'role.redirect:Admin,Doctor,Assistant'])->group(function () {
    Route::resource('vitals', VitalController::class);
    Route::resource('medicine-doses', MedicineDoseController::class);
    Route::resource('manufacturers', ManufacturerController::class)->except(['create', 'edit', 'show']);
    Route::resource('medicine-durations', MedicineDurationController::class);
    Route::resource('medicines', MedicineController::class);
});
