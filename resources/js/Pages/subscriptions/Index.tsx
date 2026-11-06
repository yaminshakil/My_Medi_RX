import { Head, router } from "@inertiajs/react";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import AppLayout from "@/Layouts/app-layout";
import { BreadcrumbItem } from "../../types";

type Plan = {
    id: number;
    name: string;
    description: string | null;
    price: string;
    billing_cycle: string;
    duration_days: number;
    features: Record<string, unknown> | null;
};
type Props = {
    plans: Plan[];
    current: {
        status: string;
        ends_at: string | null;
        plan: Plan;
    } | null;
};
export default function Index({ plans, current }: Props) {
    const checkout = (id: number) =>
        router.post(route("subscriptions.checkout", id));
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: "Subscriptions",
            href: "/subscriptions",
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Subscriptions" />
            <div className="mx-auto w-full space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Subscription plans
                    </h1>
                    <p className="text-muted-foreground">
                        Choose a plan for your Medirx account.
                    </p>
                </div>
                {current && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Current subscription: {current.plan.name}
                            </CardTitle>
                            <CardDescription>
                                Status: {current.status} · Expires:{" "}
                                {current.ends_at ?? "Not activated"}
                            </CardDescription>
                        </CardHeader>
                    </Card>
                )}
                <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {plans.map((plan) => (
                        <Card key={plan.id}>
                            <CardHeader>
                                <CardTitle>{plan.name}</CardTitle>
                                <CardDescription>
                                    {plan.description}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <p className="text-3xl font-bold">
                                    ৳
                                    {Number(plan.price).toLocaleString("en-BD")}
                                    <span className="text-sm font-normal text-muted-foreground">
                                        {" "}
                                        / {plan.billing_cycle}
                                    </span>
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {plan.duration_days} days
                                </p>
                                <ul className="space-y-1 text-sm">
                                    {Object.entries(plan.features ?? {})
                                        .filter(([, v]) => Boolean(v))
                                        .map(([k]) => (
                                            <li key={k}>
                                                ✓ {k.replaceAll("_", " ")}
                                            </li>
                                        ))}
                                </ul>
                                <Button
                                    className="w-full"
                                    onClick={() => checkout(plan.id)}
                                >
                                    Continue to payment
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
