<?php

namespace App\Http\Controllers\Admin\Hospitals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hospital\DoctorAvailabilityRequest;
use App\Http\Requests\Hospital\DoctorLeaveRequest;
use App\Http\Requests\Hospital\DoctorRosterRequest;
use App\Http\Requests\Hospital\DoctorShiftRequest;
use App\Models\Department;
use App\Models\DoctorAvailability;
use App\Models\DoctorLeave;
use App\Models\DoctorRoster;
use App\Models\DoctorShift;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DoctorRosterController extends Controller
{
    private function assertHospitalAccess(Request $request, int $hospitalId): void
    {
        // Replace/extend this with your existing authorizeHospital() helper if present.
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->can('super admin') || $user->can('manage all hospitals')) {
            return;
        }

        // If your app stores hospital_id on the admin user, this check works as-is.
        // Otherwise replace it with your existing hospital membership check.
        if (isset($user->hospital_id)) {
            abort_unless((int) $user->hospital_id === $hospitalId, 403);
        } elseif (!$user->can('manage doctor shifts')
            && !$user->can('manage doctor availability')
            && !$user->can('manage doctor rosters')
            && !$user->can('approve doctor leaves')) {
            abort(403);
        }
    }

    private function assertDoctorDepartment(int $hospitalId, int $departmentId, int $doctorId): void
    {
        $department = Department::query()->whereKey($departmentId)
            ->where('hospital_id', $hospitalId)->first();
        abort_unless($department, 422, 'Department does not belong to this hospital.');

        // The current staff-mapping feature uses a department-doctor pivot.
        // Confirm the relationship name in your Department model; change doctors() if needed.
        $assigned = method_exists($department, 'doctors')
            ? $department->doctors()->where('users.id', $doctorId)->exists()
            : User::query()->whereKey($doctorId)->exists();

        abort_unless($assigned, 422, 'Doctor is not assigned to the selected department.');
    }

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('view doctor availability')
            || $request->user()?->can('manage doctor availability')
            || $request->user()?->can('manage doctor rosters')
            || $request->user()?->can('manage doctor shifts')
            || $request->user()?->can('approve doctor leaves'), 403);

        $hospitalId = $request->integer('hospital_id');
        if ($hospitalId) {
            $this->assertHospitalAccess($request, $hospitalId);
        }

        return Inertia::render('Admin/Hospitals/DoctorRoster/Index', [
            'shifts' => DoctorShift::query()->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId))
                ->orderBy('name')->get(),
            'availabilities' => DoctorAvailability::query()->with(['doctor:id,name', 'department:id,name', 'shift:id,name'])
                ->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId))
                ->orderBy('day_of_week')->paginate(15)->withQueryString(),
            'rosters' => DoctorRoster::query()->with(['doctor:id,name', 'department:id,name', 'shift:id,name'])
                ->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId))
                ->orderByDesc('roster_date')->paginate(15, ['*'], 'roster_page')->withQueryString(),
            'leaves' => DoctorLeave::query()->with(['doctor:id,name', 'approver:id,name'])
                ->when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId))
                ->orderByDesc('start_date')->paginate(15, ['*'], 'leave_page')->withQueryString(),
            'filters' => ['hospital_id' => $hospitalId ?: null],
            'days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'hospitals' => Hospital::query()->select('id', 'hospital_name')->orderBy('hospital_name')->get(),
        ]);
    }

    public function storeShift(DoctorShiftRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $data['hospital_id']);
        DoctorShift::create($data);
        return back()->with('success', 'Shift created successfully.');
    }

    public function updateShift(DoctorShiftRequest $request, DoctorShift $shift): RedirectResponse
    {
        $this->assertHospitalAccess($request, (int) $shift->hospital_id);
        abort_unless((int) $request->validated()['hospital_id'] === (int) $shift->hospital_id, 422, 'Hospital cannot be changed.');
        $shift->update($request->validated());
        return back()->with('success', 'Shift updated successfully.');
    }

    public function deleteShift(Request $request, DoctorShift $shift): RedirectResponse
    {
        abort_unless($request->user()?->can('manage doctor shifts'), 403);
        $this->assertHospitalAccess($request, (int) $shift->hospital_id);
        abort_if($shift->availabilities()->exists() || $shift->rosters()->exists(), 422, 'Remove shift assignments before deleting this shift.');
        $shift->delete();
        return back()->with('success', 'Shift deleted.');
    }

    public function storeAvailability(DoctorAvailabilityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $data['hospital_id']);
        $this->assertDoctorDepartment((int) $data['hospital_id'], (int) $data['department_id'], (int) $data['doctor_id']);
        $this->assertShiftHospital($data['shift_id'] ?? null, (int) $data['hospital_id']);
        $this->assertAvailabilityNoOverlap($data);
        DoctorAvailability::create($data);
        return back()->with('success', 'Weekly availability saved.');
    }

    public function updateAvailability(DoctorAvailabilityRequest $request, DoctorAvailability $availability): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $availability->hospital_id);
        abort_unless((int) $data['hospital_id'] === (int) $availability->hospital_id, 422, 'Hospital cannot be changed.');
        $this->assertDoctorDepartment((int) $data['hospital_id'], (int) $data['department_id'], (int) $data['doctor_id']);
        $this->assertShiftHospital($data['shift_id'] ?? null, (int) $data['hospital_id']);
        $this->assertAvailabilityNoOverlap($data, $availability->id);
        $availability->update($data);
        return back()->with('success', 'Weekly availability updated.');
    }

    public function deleteAvailability(Request $request, DoctorAvailability $availability): RedirectResponse
    {
        abort_unless($request->user()?->can('manage doctor availability'), 403);
        $this->assertHospitalAccess($request, (int) $availability->hospital_id);
        $availability->delete();
        return back()->with('success', 'Availability deleted.');
    }

    public function storeRoster(DoctorRosterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $data['hospital_id']);
        $this->assertDoctorDepartment((int) $data['hospital_id'], (int) $data['department_id'], (int) $data['doctor_id']);
        $this->assertShiftHospital($data['shift_id'] ?? null, (int) $data['hospital_id']);
        $this->assertRosterAvailable($data);
        DoctorRoster::create($data + ['created_by' => $request->user()->id]);
        return back()->with('success', 'Roster duty assigned.');
    }

    public function updateRoster(DoctorRosterRequest $request, DoctorRoster $roster): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $roster->hospital_id);
        abort_unless((int) $data['hospital_id'] === (int) $roster->hospital_id, 422, 'Hospital cannot be changed.');
        $this->assertDoctorDepartment((int) $data['hospital_id'], (int) $data['department_id'], (int) $data['doctor_id']);
        $this->assertShiftHospital($data['shift_id'] ?? null, (int) $data['hospital_id']);
        $this->assertRosterAvailable($data, $roster->id);
        $roster->update($data);
        return back()->with('success', 'Roster updated.');
    }

    public function deleteRoster(Request $request, DoctorRoster $roster): RedirectResponse
    {
        abort_unless($request->user()?->can('manage doctor rosters'), 403);
        $this->assertHospitalAccess($request, (int) $roster->hospital_id);
        $roster->delete();
        return back()->with('success', 'Roster deleted.');
    }

    public function storeLeave(DoctorLeaveRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->assertHospitalAccess($request, (int) $data['hospital_id']);
        abort_unless(User::whereKey($data['doctor_id'])->exists(), 422);
        $data['status'] = 'pending';
        DoctorLeave::create($data);
        return back()->with('success', 'Leave request submitted for approval.');
    }

    public function approveLeave(Request $request, DoctorLeave $leave): RedirectResponse
    {
        abort_unless($request->user()?->can('approve doctor leaves'), 403);
        $this->assertHospitalAccess($request, (int) $leave->hospital_id);
        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending leave requests can be approved.']);
        }

        // Refuse approval if it conflicts with booked duties. Review appointments before extending this rule.
        $conflict = DoctorRoster::where('doctor_id', $leave->doctor_id)
            ->where('hospital_id', $leave->hospital_id)
            ->where('status', 'scheduled')
            ->whereBetween('roster_date', [$leave->start_date, $leave->end_date])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['leave' => 'This leave overlaps a scheduled duty. Reassign or cancel that duty first.']);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Leave approved.');
    }

    public function rejectLeave(Request $request, DoctorLeave $leave): RedirectResponse
    {
        abort_unless($request->user()?->can('approve doctor leaves'), 403);
        $this->assertHospitalAccess($request, (int) $leave->hospital_id);
        abort_unless($leave->status === 'pending', 422, 'Only pending leave requests can be rejected.');
        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'review_note' => $request->string('review_note')->toString(),
        ]);
        return back()->with('success', 'Leave rejected.');
    }

    private function assertShiftHospital(?int $shiftId, int $hospitalId): void
    {
        if (!$shiftId) {
            return;
        }
        abort_unless(DoctorShift::whereKey($shiftId)->where('hospital_id', $hospitalId)->exists(), 422, 'Shift does not belong to this hospital.');
    }

    private function assertAvailabilityNoOverlap(array $data, ?int $ignoreId = null): void
    {
        $overlap = DoctorAvailability::where('hospital_id', $data['hospital_id'])
            ->where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('is_available', true)
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($data['is_available'] && $overlap) {
            throw ValidationException::withMessages(['start_time' => 'This time overlaps another weekly availability entry.']);
        }
    }

    private function assertRosterAvailable(array $data, ?int $ignoreId = null): void
    {
        if ($data['status'] === 'cancelled') {
            return;
        }

        $roster = DoctorRoster::where('hospital_id', $data['hospital_id'])
            ->where('doctor_id', $data['doctor_id'])
            ->whereDate('roster_date', $data['roster_date'])
            ->where('status', 'scheduled')
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        $leave = DoctorLeave::where('hospital_id', $data['hospital_id'])
            ->where('doctor_id', $data['doctor_id'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $data['roster_date'])
            ->whereDate('end_date', '>=', $data['roster_date'])
            ->exists();

        if ($roster) {
            throw ValidationException::withMessages(['start_time' => 'Doctor already has an overlapping roster duty.']);
        }
        if ($leave) {
            throw ValidationException::withMessages(['roster_date' => 'Doctor is on approved leave for this date.']);
        }
    }
}
