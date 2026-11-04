<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/subscriptions/Plans/Index', [
            'plans' => SubscriptionPlan::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:10','max:500000'],
            'billing_cycle' => ['required','in:monthly,quarterly,yearly,custom'],
            'duration_days' => ['required','integer','min:1','max:3650'],
            'user_type' => ['required','in:All,Patient,Doctor,Pharmacy,Hospital'],
            'features' => ['nullable','array'],
            'is_active' => ['sometimes','boolean'],
        ]);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $data['is_active'] = $data['is_active'] ?? true;
        SubscriptionPlan::create($data);
        return back()->with('success','Subscription plan created.');
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:10','max:500000'],
            'billing_cycle' => ['required','in:monthly,quarterly,yearly,custom'],
            'duration_days' => ['required','integer','min:1','max:3650'],
            'user_type' => ['required','in:All,Patient,Doctor,Pharmacy,Hospital'],
            'features' => ['nullable','array'],
            'is_active' => ['required','boolean'],
        ]);
        $plan->update($data);
        return back()->with('success','Subscription plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        abort_if($plan->subscriptions()->exists(), 409, 'This plan has subscriptions. Deactivate it instead.');
        $plan->delete();
        return back()->with('success','Subscription plan deleted.');
    }
}
