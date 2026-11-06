import { Head, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/app-layout";
import { BreadcrumbItem } from "../../types";
export default function Result({ message }: { message: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: "Subscriptions",
            href: "/subscriptions",
        },
        {
            title: "Payment status",
            href: "/subscriptions/result",
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Payment status" />
            <div className="mx-auto max-w-xl space-y-4 p-8 text-center">
                <h1 className="text-2xl font-semibold">Payment status</h1>
                <p className="text-muted-foreground">{message}</p>
                <div className="flex justify-center gap-3">
                    <Link
                        className="underline"
                        href={route("subscriptions.index")}
                    >
                        Subscriptions
                    </Link>
                    <Link
                        className="underline"
                        href={route("subscriptions.history")}
                    >
                        Payment history
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
