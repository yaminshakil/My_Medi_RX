import { Head } from '@inertiajs/react';
import AppLayout from "@/Layouts/app-layout";
import { BreadcrumbItem } from "../../types";

type Payment={
    id:number;
    transaction_id:string;
    amount:string;
    currency:string;
    status:string;
    paid_at:string|null;
    subscription:{
        plan:{
            name:string
        }
    }
};
export default function History({payments}:{payments:{data:Payment[]}}){
    const breadcrumbs: BreadcrumbItem[] = [
            {
                title: "Subscriptions",
                href: "/subscriptions",
            },
            {
            title: "Payment history",
            href: "/subscriptions/history",
        },
        ];
    return (
    <AppLayout breadcrumbs={breadcrumbs}>
    <Head title="Payment history"/>
    <div className="p-6">
        <h1 className="mb-5 text-2xl font-semibold">Payment history</h1>
        <div className="overflow-x-auto rounded-lg border">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b text-left">
                        <th className="p-3">Plan</th>
                        <th className="p-3">Transaction</th>
                        <th className="p-3">Amount</th>
                        <th className="p-3">Status</th>
                        <th className="p-3">Paid at</th>
                    </tr>
                </thead>
                <tbody>{payments.data.map(p=>
                    <tr className="border-b" key={p.id}>
                        <td className="p-3">{p.subscription.plan.name}</td>
                        <td className="p-3">{p.transaction_id}</td>
                        <td className="p-3">{p.currency} {p.amount}</td>
                        <td className="p-3">{p.status}</td>
                        <td className="p-3">{p.paid_at ?? '—'}</td>
                        </tr>
                        )}
                </tbody>
            </table>
        </div>
    </div>
    </AppLayout>
)
}
