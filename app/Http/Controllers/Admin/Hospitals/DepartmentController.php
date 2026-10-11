<?php

namespace App\Http\Controllers\Admin\Hospitals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hospital\DepartmentStoreRequest;
use App\Http\Requests\Hospital\DepartmentUpdateRequest;
use App\Models\Department;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Department::query()
            ->with('hospital:id,hospital_name')
            ->withCount('doctors');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('floor', 'like', "%{$search}%")
                    ->orWhereHas('hospital', fn ($h) => $h->where('hospital_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->integer('hospital_id'));
        }

        if ($request->query('status') !== null && $request->query('status') !== '') {
            $query->where('status', $request->boolean('status'));
        }

        $perPage = min(max($request->integer('per_page', 10), 1), 100);
        $departments = $query->orderBy('sort_order')->orderBy('name')
            ->paginate($perPage)->withQueryString();

        // Keep the shared UI convention: items.links.map(...)
        $items = [
            'data' => $departments->items(),
            'links' => $departments->linkCollection()->toArray(),
            'current_page' => $departments->currentPage(),
            'last_page' => $departments->lastPage(),
            'per_page' => $departments->perPage(),
            'total' => $departments->total(),
            'from' => $departments->firstItem(),
            'to' => $departments->lastItem(),
        ];

        return Inertia::render('Admin/Departments/Index', [
            'items' => $items,
            'filters' => $request->only(['search', 'hospital_id', 'status', 'per_page']),
            'hospitals' => Hospital::query()->select('id', 'hospital_name')->orderBy('hospital_name')->get(),
            'can' => [
                'create' => $request->user()?->can('create departments') ?? false,
                'edit' => $request->user()?->can('edit departments') ?? false,
                'delete' => $request->user()?->can('delete departments') ?? false,
                'assignDoctors' => $request->user()?->can('assign department doctors') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Departments/Create', [
            'hospitals' => Hospital::query()->select('id', 'hospital_name')->orderBy('hospital_name')->get(),
        ]);
    }

    public function store(DepartmentStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->authorizeHospital($request, (int) $data['hospital_id']);

        $data['uuid'] = (string) Str::uuid();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Department::create($data);

        return to_route('department.index')->with('success', 'Department created successfully.');
    }

    public function show(Request $request, Department $department): Response
    {
        $this->authorizeHospital($request, (int) $department->hospital_id);
        $department->load([
            'hospital:id,hospital_name',
            'doctors' => fn ($query) => $query->orderBy('name'),
        ]);

        $availableDoctors = User::query()
            ->select('id', 'name', 'email')
            ->whereDoesntHave('departments', fn ($q) => $q->where('departments.id', $department->id))
            ->orderBy('name')->limit(500)->get();

        return Inertia::render('Admin/Departments/Show', [
            'department' => $department,
            'availableDoctors' => $availableDoctors,
            'canAssignDoctors' => $request->user()?->can('assign department doctors') ?? false,
        ]);
    }

    public function edit(Request $request, Department $department): Response
    {
        $this->authorizeHospital($request, (int) $department->hospital_id);

        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
            'hospitals' => Hospital::query()->select('id', 'hospital_name')->orderBy('hospital_name')->get(),
        ]);
    }

    public function update(DepartmentUpdateRequest $request, Department $department): RedirectResponse
    {
        $data = $request->validated();
        $this->authorizeHospital($request, (int) $department->hospital_id);
        $this->authorizeHospital($request, (int) $data['hospital_id']);

        $department->update($data);

        return to_route('department.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeHospital($request, (int) $department->hospital_id);
        abort_unless($request->user()?->can('delete departments'), 403);

        $department->delete();

        return to_route('department.index')->with('success', 'Department deleted successfully.');
    }

    public function assignDoctor(Request $request, Department $department): RedirectResponse
    {
        abort_unless($request->user()?->can('assign department doctors'), 403);
        $this->authorizeHospital($request, (int) $department->hospital_id);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'extension_number' => ['nullable', 'string', 'max:50'],
            'is_department_head' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($department, $data) {
            if ($data['is_department_head']) {
                DB::table('department_doctor')
                    ->where('department_id', $department->id)
                    ->update(['is_department_head' => false]);
            }

            $department->doctors()->syncWithoutDetaching([
                $data['user_id'] => [
                    'room_number' => $data['room_number'] ?? null,
                    'extension_number' => $data['extension_number'] ?? null,
                    'is_department_head' => $data['is_department_head'],
                    'status' => $data['status'],
                ],
            ]);
        });

        return back()->with('success', 'Doctor assigned to department.');
    }

    public function updateDoctor(Request $request, Department $department, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('assign department doctors'), 403);
        $this->authorizeHospital($request, (int) $department->hospital_id);

        $data = $request->validate([
            'room_number' => ['nullable', 'string', 'max:50'],
            'extension_number' => ['nullable', 'string', 'max:50'],
            'is_department_head' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        abort_unless($department->doctors()->whereKey($user->id)->exists(), 404);

        DB::transaction(function () use ($department, $user, $data) {
            if ($data['is_department_head']) {
                DB::table('department_doctor')
                    ->where('department_id', $department->id)
                    ->where('user_id', '!=', $user->id)
                    ->update(['is_department_head' => false]);
            }

            $department->doctors()->updateExistingPivot($user->id, $data);
        });

        return back()->with('success', 'Doctor assignment updated.');
    }

    public function removeDoctor(Request $request, Department $department, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('assign department doctors'), 403);
        $this->authorizeHospital($request, (int) $department->hospital_id);

        $department->doctors()->detach($user->id);

        return back()->with('success', 'Doctor removed from department.');
    }

    private function authorizeHospital(Request $request, int $hospitalId): void
    {
        // Replace/extend this check with your application's hospital ownership policy.
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->can('manage all hospitals')) {
            return;
        }

        abort_unless(
            $user->can('manage departments') || $user->can('manage hospitals'),
            403,
            'You are not allowed to manage this hospital.'
        );
    }
}
