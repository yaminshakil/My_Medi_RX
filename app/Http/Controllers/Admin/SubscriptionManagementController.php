<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Inertia\Inertia;

class SubscriptionManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/subscriptions/Index', [
            'subscriptions' => Subscription::with(['user:id,name,email', 'plan:id,name'])
                ->latest()->paginate(20),
        ]);
    }

    public function payments()
    {
        return Inertia::render('admin/subscriptions/Payments/Index', [
            'payments' => SubscriptionPayment::with(['user:id,name,email', 'subscription.plan:id,name'])
                ->latest()->paginate(20),
        ]);
    }
}
