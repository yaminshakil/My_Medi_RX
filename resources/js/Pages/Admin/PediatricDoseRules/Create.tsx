import { Head } from "@inertiajs/react";
import { Link } from "@inertiajs/react";

import RuleForm, {
    MedicineOption,
} from "./components/RuleForm";

import { Button } from "@/Components/ui/button";
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Pediatric Dose Rule', href: '/admin/pediatric-dose-rules/create' }];

type Props = {
    medicines: MedicineOption[];
};

export default function Create({ medicines }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Pediatric Dose Rule" />

            <div className="mx-auto w-full space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Create Pediatric Dose Rule
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Create a draft for clinical review.
                        </p>
                    </div>

                    <Button asChild variant="outline">
                        <Link href={route("admin.pediatric-dose-rules.index")}>
                            Back to Rules
                        </Link>
                    </Button>
                </div>

                <RuleForm
                    medicines={medicines}
                    submitUrl={route("admin.pediatric-dose-rules.store")}
                    submitLabel="Save Draft"
                />
            </div>
        </AppLayout>
    );
}
