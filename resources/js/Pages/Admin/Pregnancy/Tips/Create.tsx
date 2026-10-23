import AppLayout from "@/Layouts/app-layout";
import { Head } from "@inertiajs/react";
import TipForm from "./Form";

export default function Create() {
    return (
        <AppLayout>
            <Head title="Create Pregnancy Tip" />
            <div className="space-y-6 p-6">
                <h1 className="text-2xl font-semibold">Create Pregnancy Tip</h1>
                <TipForm />
            </div>
        </AppLayout>
    );
}
