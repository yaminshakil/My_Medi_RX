import { useEffect, useState } from "react";
import {
    activateFreeSubscription,
    getCurrentSubscription,
    getSubscriptionPlans,
    startSubscriptionCheckout,
    type SubscriptionPlan,
} from "@/lib/subscription-api";

export default function ApiPlansExample() {
    const [plans, setPlans] = useState<SubscriptionPlan[]>([]);
    const [current, setCurrent] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    const load = async () => {
        setLoading(true);
        try {
            const [planResponse, currentResponse] = await Promise.all([
                getSubscriptionPlans(),
                getCurrentSubscription(),
            ]);
            setPlans(planResponse.data.data ?? []);
            setCurrent(currentResponse.data.data ?? null);
        } catch (e: any) {
            setError(
                e.response?.data?.message ??
                    "Unable to load subscription information.",
            );
        } finally {
            setLoading(false);
        }
    };
    n;
    useEffect(() => {
        void load();
    }, []);

    const choosePlan = async (plan: SubscriptionPlan) => {
        setError("");
        try {
            if (plan.price === 0) {
                await activateFreeSubscription(plan.id);
                await load();
                return;
            }
            const response = await startSubscriptionCheckout(plan.id);
            const url = response.data.data?.gateway_url;
            if (!url)
                throw new Error("SSLCommerz did not return a checkout URL.");
            window.location.assign(url);
        } catch (e: any) {
            setError(
                e.response?.data?.message ??
                    e.message ??
                    "Unable to start checkout.",
            );
        }
    };

    if (loading)
        return <div className="p-6">Loading subscription plans...</div>;

    return (
        <div className="space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold">Subscription Plans</h1>
                {current ? (
                    <p className="text-sm text-muted-foreground">
                        Current plan: {current.plan?.name} · Expires:{" "}
                        {current.ends_at}
                    </p>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        No active subscription.
                    </p>
                )}
            </div>
            {error && (
                <div
                    role="alert"
                    className="rounded-md border border-destructive p-3 text-destructive"
                >
                    {error}
                </div>
            )}
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {plans.map((plan) => (
                    <article
                        key={plan.id}
                        className="space-y-3 rounded-xl border p-5"
                    >
                        <h2 className="text-lg font-semibold">{plan.name}</h2>
                        <p className="text-sm text-muted-foreground">
                            {plan.description}
                        </p>
                        <p className="text-2xl font-bold">
                            ৳{plan.price}
                            <span className="text-sm font-normal">
                                {" "}
                                / {plan.billing_cycle}
                            </span>
                        </p>
                        <p className="text-xs text-muted-foreground">
                            {plan.duration_days} days · {plan.user_type}
                        </p>
                        <button
                            type="button"
                            onClick={() => void choosePlan(plan)}
                            className="w-full rounded-md bg-primary px-4 py-2 text-primary-foreground"
                        >
                            {plan.price === 0
                                ? "Activate Free Plan"
                                : "Subscribe"}
                        </button>
                    </article>
                ))}
            </div>
        </div>
    );
}
