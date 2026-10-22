import { Head, Link } from "@inertiajs/react";

import RuleForm, {
    MedicineOption,
    RuleFormData,
} from "./components/RuleForm";

import { Button } from "@/Components/ui/button";
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

type Rule = {
    id: number;
} & Record<string, unknown>;

type Props = {
    rule: Rule;
    medicines: MedicineOption[];
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Pediatric Dose Rule', href: '/admin/pediatric-dose-rules/create' }];

const editableFields: (keyof RuleFormData)[] = [
    "medicine_id",
    "formulation",
    "indication",
    "concentration_mg",
    "concentration_ml",
    "dose_mg_per_kg_per_dose",
    "max_single_dose_mg",
    "max_daily_mg_per_kg",
    "max_daily_dose_mg",
    "dose_interval_hours",
    "max_doses_per_24_hours",
    "min_age_months",
    "max_age_months",
    "min_weight_kg",
    "max_weight_kg",
    "route",
    "contraindications",
    "notes",
    "source_reference",
    "source_version",
];

export default function Edit({ rule, medicines }: Props) {
    const initialData = Object.fromEntries(
        editableFields.map((field) => [
            field,
            rule[field] == null ? "" : String(rule[field]),
        ]),
    ) as Partial<RuleFormData>;

    return (
        <AppLayout>
            <Head title="Edit Pediatric Dose Rule" />

            <div className="mx-auto max-w-6xl space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Edit Pediatric Dose Rule
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Update the draft, then submit it for review.
                        </p>
                    </div>

                    <Button asChild variant="outline">
                        <Link
                            href={route(
                                "admin.pediatric-dose-rules.show",
                                rule.id,
                            )}
                        >
                            Cancel
                        </Link>
                    </Button>
                </div>

                <RuleForm
                    medicines={medicines}
                    initialData={initialData}
                    submitUrl={route(
                        "admin.pediatric-dose-rules.update",
                        rule.id,
                    )}
                    method="put"
                    submitLabel="Update Draft"
                />
            </div>
        </AppLayout>
    );
}
