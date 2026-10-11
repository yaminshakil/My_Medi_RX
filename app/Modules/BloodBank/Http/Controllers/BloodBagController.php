<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreBloodBagRequest;
use App\Modules\BloodBank\Http\Resources\BloodBagResource;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Repositories\Contracts\BloodBagRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use App\Modules\BloodBank\Services\BloodBagService;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodBagController extends Controller
{
    public function __construct(
        private readonly BloodBagRepositoryInterface $bags,
        private readonly StorageUnitRepositoryInterface $units,
        private readonly BloodBagService $service,
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'blood_group', 'component', 'status', 'expiry', 'storage_unit_id', 'sort', 'direction']);

        return Inertia::render('Admin/BloodBank/Bags/Index', [
            'bags'    => $this->bags->paginate($filters)->through(fn ($bag) => BloodBagResource::make($bag)->resolve()),
            'filters' => (object) $filters,
            'options' => $this->options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/BloodBank/Bags/Create', ['options' => $this->options()]);
    }

    public function store(StoreBloodBagRequest $request): RedirectResponse
    {
        $this->service->register($request->user(), $request->validated());

        return redirect()->route('blood-bank.bags.index')->with('success', 'Blood unit registered.');
    }

    public function discard(Request $request, BloodBag $bag): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $this->service->discard($bag, $data['reason']);
        } catch (BloodBankException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Unit {$bag->bag_number} discarded.");
    }

    public function release(BloodBag $bag): RedirectResponse
    {
        try {
            $this->service->release($bag);
        } catch (BloodBankException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Unit {$bag->bag_number} released to stock.");
    }

    private function options(): array
    {
        return [
            'groups'        => LookupOptions::groups(),
            'components'    => LookupOptions::components(),
            'statuses'      => LookupOptions::statuses(),
            'storage_units' => $this->units->active()->map(fn ($u) => ['value' => $u->id, 'label' => $u->name])->values(),
        ];
    }
}
