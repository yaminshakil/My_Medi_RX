<?php

namespace App\Http\Requests\Eprescription;

use Illuminate\Foundation\Http\FormRequest;

class CreateEprescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'followup_date' => 'required|date',
            'is_followup' => 'nullable|boolean',
            'diagnosis' => 'nullable|string',
            'symptoms' => 'required|array',
            'symptoms.*.name' => 'required|string',
            'medications' => 'required|array|min:1',
            'medications.*.type' => 'required|string',
            'medications.*.medicine_id' => 'required|exists:medicines,id',
            'medications.*.strength' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'nullable|string',
            'medications.*.duration' => 'required|string',
            'medications.*.advice' => 'nullable|string',
            'medications.*.meal_time' => 'nullable|string',
            'vital_id' => 'nullable|exists:vitals,id',
            'instructions' => 'nullable|string',
            'follow_up_advice' => 'nullable|string',
            'status' => 'required|in:draft,issued,completed,cancelled',
            'onexaminations' => 'nullable|array',
            'investigations' => 'nullable|array',
            'appointment_id' => 'required|exists:appointments,id',
            // Nested gynae_history rules
            'gynae_history' => 'nullable|array',
            'gynae_history.marital_status' => 'nullable|string|max:255',
            'gynae_history.marriage_duration' => 'nullable|string|max:255',
            'gynae_history.consanguinity' => 'nullable|string|max:255',
            'gynae_history.menarche_age' => 'nullable|string|max:10',
            'gynae_history.lmp' => 'nullable|date',
            'gynae_history.cycle' => 'nullable|string|max:255',
            'gynae_history.flow' => 'nullable|string|max:255',
            'gynae_history.dysmenorrhea' => 'nullable|boolean',
            'gynae_history.contraceptive_use' => 'nullable|boolean',
            'gynae_history.gravida' => 'nullable|integer',
            'gynae_history.para' => 'nullable|integer',
            'gynae_history.abortion' => 'nullable|integer',
            'gynae_history.living_children' => 'nullable|integer',
            'gynae_history.edd' => 'nullable|date',
            'gynae_history.anc' => 'nullable|string|max:100',
            'gynae_history.other_history' => 'nullable|string|max:1000',
        ];
    }
}
