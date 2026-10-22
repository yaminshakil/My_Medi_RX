import { FormEvent } from "react";
import { useForm } from "@inertiajs/react";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

export type MedicineOption = {
    value: string;
    label: string;
};

export type RuleFormData = {
    medicine_id: string;
    formulation: string;
    indication: string;
    concentration_mg: string;
    concentration_ml: string;
    dose_mg_per_kg_per_dose: string;
    max_single_dose_mg: string;
    max_daily_mg_per_kg: string;
    max_daily_dose_mg: string;
    dose_interval_hours: string;
    max_doses_per_24_hours: string;
    min_age_months: string;
    max_age_months: string;
    min_weight_kg: string;
    max_weight_kg: string;
    route: string;
    contraindications: string;
    notes: string;
    source_reference: string;
    source_version: string;
};

const emptyForm: RuleFormData = {
    medicine_id: "",
    formulation: "",
    indication: "",
    concentration_mg: "",
    concentration_ml: "",
    dose_mg_per_kg_per_dose: "",
    max_single_dose_mg: "",
    max_daily_mg_per_kg: "",
    max_daily_dose_mg: "",
    dose_interval_hours: "",
    max_doses_per_24_hours: "",
    min_age_months: "0",
    max_age_months: "",
    min_weight_kg: "",
    max_weight_kg: "",
    route: "oral",
    contraindications: "",
    notes: "",
    source_reference: "",
    source_version: "",
};

type Props = {
    medicines: MedicineOption[];
    initialData?: Partial<RuleFormData>;
    submitUrl: string;
    method?: "post" | "put";
    submitLabel: string;
};

export default function RuleForm({
    medicines,
    initialData,
    submitUrl,
    method = "post",
    submitLabel,
}: Props) {
    const form = useForm<RuleFormData>({
        ...emptyForm,
        ...initialData,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (method === "put") {
            form.put(submitUrl);
        } else {
            form.post(submitUrl);
        }
    }

    function field(
        name: keyof RuleFormData,
        label: string,
        options: {
            type?: string;
            required?: boolean;
            placeholder?: string;
            help?: string;
        } = {},
    ) {
        const error = form.errors[name];

        return (
            <div className="space-y-2" key={name}>
                <label htmlFor={name} className="text-sm font-medium">
                    {label}
                    {options.required && (
                        <span className="text-destructive"> *</span>
                    )}
                </label>

                <Input
                    id={name}
                    type={options.type ?? "text"}
                    value={form.data[name]}
                    placeholder={options.placeholder}
                    onChange={(event) =>
                        form.setData(name, event.target.value)
                    }
                    aria-invalid={Boolean(error)}
                />

                {options.help && (
                    <p className="text-xs text-muted-foreground">
                        {options.help}
                    </p>
                )}

                {error && (
                    <p className="text-sm text-destructive">{error}</p>
                )}
            </div>
        );
    }

    function textarea(
        name: "contraindications" | "notes" | "source_reference",
        label: string,
        placeholder: string,
    ) {
        return (
            <div className="space-y-2" key={name}>
                <label htmlFor={name} className="text-sm font-medium">
                    {label} <span className="text-destructive">*</span>
                </label>
                <Textarea
                    id={name}
                    value={form.data[name]}
                    placeholder={placeholder}
                    onChange={(event) =>
                        form.setData(name, event.target.value)
                    }
                    aria-invalid={Boolean(form.errors[name])}
                    rows={3}
                />
                {form.errors[name] && (
                    <p className="text-sm text-destructive">
                        {form.errors[name]}
                    </p>
                )}
            </div>
        );
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Medicine Information</CardTitle>
                </CardHeader>

                <CardContent className="grid gap-5 md:grid-cols-2">
                    <div className="space-y-2">
                        <label className="text-sm font-medium">
                            Medicine <span className="text-destructive">*</span>
                        </label>
                        <Select
                            value={form.data.medicine_id}
                            onValueChange={(value) =>
                                form.setData("medicine_id", value)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select medicine" />
                            </SelectTrigger>
                            <SelectContent>
                                {medicines.map((medicine) => (
                                    <SelectItem
                                        key={medicine.value}
                                        value={medicine.value}
                                    >
                                        {medicine.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.medicine_id && (
                            <p className="text-sm text-destructive">
                                {form.errors.medicine_id}
                            </p>
                        )}
                    </div>

                    {field("formulation", "Formulation", {
                        required: true,
                        placeholder: "Exact formulation and strength",
                    })}

                    {field("indication", "Clinical indication", {
                        required: true,
                        placeholder: "Clinician-reviewed indication",
                    })}

                    {field("route", "Route", {
                        required: true,
                        placeholder: "oral",
                    })}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Concentration and Dose Parameters</CardTitle>
                </CardHeader>

                <CardContent className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {field("concentration_mg", "Concentration (mg)", {
                        type: "number",
                        required: true,
                        placeholder: "Verified value",
                    })}

                    {field("concentration_ml", "Concentration volume (mL)", {
                        type: "number",
                        required: true,
                        placeholder: "Verified volume",
                    })}

                    {field(
                        "dose_mg_per_kg_per_dose",
                        "Approved mg/kg per dose",
                        {
                            type: "number",
                            required: true,
                            placeholder: "Clinically verified value",
                        },
                    )}

                    {field("max_single_dose_mg", "Maximum single dose (mg)", {
                        type: "number",
                    })}

                    {field("max_daily_mg_per_kg", "Maximum daily mg/kg", {
                        type: "number",
                    })}

                    {field("max_daily_dose_mg", "Maximum daily dose (mg)", {
                        type: "number",
                    })}

                    {field("dose_interval_hours", "Interval (hours)", {
                        type: "number",
                    })}

                    {field(
                        "max_doses_per_24_hours",
                        "Maximum doses per 24 hours",
                        { type: "number" },
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Eligibility and Clinical Source</CardTitle>
                </CardHeader>

                <CardContent className="space-y-5">
                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        {field("min_age_months", "Minimum age (months)", {
                            type: "number",
                            required: true,
                        })}
                        {field("max_age_months", "Maximum age (months)", {
                            type: "number",
                        })}
                        {field("min_weight_kg", "Minimum weight (kg)", {
                            type: "number",
                        })}
                        {field("max_weight_kg", "Maximum weight (kg)", {
                            type: "number",
                        })}
                    </div>

                    <div className="grid gap-5 md:grid-cols-2">
                        {textarea(
                            "source_reference",
                            "Clinical source reference",
                            "Guideline/document title, URL or citation and relevant section",
                        )}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">
                                Source version
                            </label>
                            <Input
                                value={form.data.source_version}
                                onChange={(event) =>
                                    form.setData(
                                        "source_version",
                                        event.target.value,
                                    )
                                }
                                placeholder="Guideline edition or publication date"
                            />
                        </div>
                    </div>

                    {textarea(
                        "contraindications",
                        "Contraindications and warnings",
                        "Document known exclusions and safety warnings",
                    )}

                    <div className="space-y-2">
                        <label className="text-sm font-medium">
                            Clinical notes
                        </label>
                        <Textarea
                            value={form.data.notes}
                            onChange={(event) =>
                                form.setData("notes", event.target.value)
                            }
                            placeholder="Additional review notes"
                            rows={3}
                        />
                    </div>
                </CardContent>
            </Card>

            <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Button
                    type="button"
                    variant="outline"
                    onClick={() => window.history.back()}
                >
                    Cancel
                </Button>

                <Button type="submit" disabled={form.processing}>
                    {form.processing ? "Saving..." : submitLabel}
                </Button>
            </div>
        </form>
    );
}
