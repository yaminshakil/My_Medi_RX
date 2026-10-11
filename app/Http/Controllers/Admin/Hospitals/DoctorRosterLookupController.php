<?php

namespace App\Http\Controllers\Admin\Hospitals;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorRosterLookupController extends Controller
{
    public function departments(Hospital $hospital)
    {
        abort_unless(
            auth()->user()?->can('view doctor availability') ||
            auth()->user()?->can('manage doctor availability') ||
            auth()->user()?->can('manage doctor rosters') ||
            auth()->user()?->can('manage doctor shifts'),
            403
        );

        // Apply your existing authorizeHospital() check here.

        return response()->json([
            'data' => Department::query()
                ->where('hospital_id', $hospital->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function doctors(Request $request, Department $department)
    {
        abort_unless(
            auth()->user()?->can('view doctor availability') ||
            auth()->user()?->can('manage doctor availability') ||
            auth()->user()?->can('manage doctor rosters') ||
            auth()->user()?->can('manage doctor shifts'),
            403
        );

        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
        ]);

        abort_unless(
            (int) $department->hospital_id === (int) $validated['hospital_id'],
            422,
            'Department does not belong to the selected hospital.'
        );

        // Uses your existing Department::doctors() relationship.
        $doctors = $department->doctors()
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'data' => $doctors,
        ]);
    }
}
