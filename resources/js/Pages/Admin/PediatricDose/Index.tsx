
import { useCallback, useEffect, useState } from "react";
import axios from "axios";
import {
    Calculator,
    Baby,
    Pill,
    History,
    Loader2,
    AlertCircle,
    CheckCircle2,
    RefreshCw,
} from "lucide-react";

import { Head, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/app-layout";

import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Separator } from "@/Components/ui/separator";

type Medicine = {
    id: number;
    name: string;
    formulations?: string[];
    indications?: string[];
};

type DoseForm = {
    medicineId: string;
    formulation: string;
    indication: string;
    childWeightKg: string;
    childAgeMonths: string;
};

type ApiResponse<T> = {
    success: boolean;
    message: string;
    data: T;
    errors?: Record<string, string[]>;
};

const initialForm: DoseForm = {
    medicineId: "",
    formulation: "",
    indication: "",
    childWeightKg: "",
    childAgeMonths: "",
};

type SharedProps = {
    auth: {
        user: any;
        jwt_token: string;
        permissions: string[];
    };
};


export default function PediatricDoseIndex() {
    const { auth } = usePage<SharedProps>().props;
    const [medicines, setMedicines] = useState<Medicine[]>([]);
    const [recent, setRecent] = useState<any[]>([]);
    const [form, setForm] = useState<DoseForm>(initialForm);

    const [result, setResult] = useState<any>(null);
    const [loading, setLoading] = useState(false);
    const [initialLoading, setInitialLoading] = useState(true);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [message, setMessage] = useState("");

    const selectedMedicine = medicines.find(
        (medicine) => String(medicine.medicineId) === form.medicineId
    );

    const api = axios.create({
    baseURL: "/api/v1",
    headers: {
        Authorization: `Bearer ${auth.jwt_token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

    const loadData = useCallback(async () => {
        setInitialLoading(true);

        try {
            const medicineResponse = await api.get("/pediatric-dose/medicines");
            setMedicines(medicineResponse.data.data ?? []);
            const recentResponse = await api.get("/pediatric-dose/recent", {
                params: { limit: 10 },
            });
            setRecent(recentResponse.data.data ?? []);
        } catch (error) {
            console.error("Unable to load pediatric dose data", error);
            setMessage("Unable to load medicines or recent calculations.");
        } finally {
            setInitialLoading(false);
        }
    }, []);
    console.log(selectedMedicine, "selected formulation");
    useEffect(() => {
        loadData();
    }, [loadData]);

    const updateField = (field: keyof DoseForm, value: string) => {
        setForm((previous) => ({
            ...previous,
            [field]: value,
        }));

        setErrors((previous) => ({
            ...previous,
            [field]: [],
        }));

        setMessage("");
    };

    const calculateDose = async (event: React.FormEvent) => {
        event.preventDefault();

        setLoading(true);
        setResult(null);
        setErrors({});
        setMessage("");

        try {
            const response = await api.post("/pediatric-dose/calculate", {
                medicineId: Number(form.medicineId),
                formulation: form.formulation,
                indication: form.indication,
                childWeightKg: Number(form.childWeightKg),
                childAgeMonths: Number(form.childAgeMonths),
            });

            if (response.data.success) {
                setResult(response.data.data);
                setMessage(response.data.message);

                const historyResponse = await api.get("/pediatric-dose/recent", {
                params: { limit: 10 },
            });

                setRecent(historyResponse.data.data ?? []);
            }
        } catch (error: any) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors ?? {});
                setMessage(error.response.data.message);
            } else {
                setMessage(
                    error.response?.data?.message ??
                    "Something went wrong. Please try again."
                );
            }
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setForm(initialForm);
        setResult(null);
        setErrors({});
        setMessage("");
    };

    const renderError = (field: keyof DoseForm) => {
        const error = errors[field];

        if (!error?.length) return null;

        return (
            <p className="text-sm text-destructive mt-1">
                {error[0]}
            </p>
        );
    };
    console.log(recent, "selected medicine");
    return (
        <AppLayout>
            <Head title="Pediatric Dose Calculator" />

            <div className="space-y-6 p-4 md:p-6">

                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Pediatric Dose Calculator
                        </h1>
                        <p className="text-muted-foreground text-sm mt-1">
                            Calculate pediatric medicine doses using approved rules.
                        </p>
                    </div>

                    <Badge variant="outline" className="w-fit gap-2 py-1.5">
                        <CheckCircle2 className="h-4 w-4 text-green-600" />
                        Approved Dose Rules
                    </Badge>
                </div>

                {message && !result && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{message}</AlertDescription>
                    </Alert>
                )}

                <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">

                    {/* Calculator */}
                    <Card className="xl:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calculator className="h-5 w-5" />
                                Calculate Medicine Dose
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            <form
                                onSubmit={calculateDose}
                                className="space-y-6"
                            >
                                {/* Medicine */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 font-semibold">
                                        <Pill className="h-5 w-5 text-primary" />
                                        Medicine Information
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Medicine *</Label>

                                        <Select
                                            value={form.medicineId}
                                            onValueChange={(value) => {
                                                setForm((previous) => ({
                                                    ...previous,
                                                    medicineId: value,
                                                    formulation: "",
                                                    indication: "",
                                                }));
                                                setResult(null);
                                            }}
                                            disabled={initialLoading}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select medicine" />
                                            </SelectTrigger>

                                            <SelectContent>
                                                {medicines.map((medicine) => (
                                                    <SelectItem
                                                        key={medicine.medicineId}
                                                        value={String(medicine.medicineId)}
                                                    >
                                                        {medicine.medicineName}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        {renderError("medicineId")}
                                    </div>


<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div className="space-y-2">
        <Label htmlFor="formulation">Formulation *</Label>

        <Input
            id="formulation"
            type="text"
            placeholder="e.g. Syrup 15 mg/5 mL"
            value={form?.formulation}
            onChange={(e) =>
                updateField("formulation", e.target.value)
            }
            maxLength={150}
        />

        {renderError("formulation")}
    </div>

    <div className="space-y-2">
        <Label htmlFor="indication">Indication *</Label>

        <Input
            id="indication"
            type="text"
            placeholder="e.g. Cough, Fever"
            value={form?.indication}
            onChange={(e) =>
                updateField("indication", e.target.value)
            }
            maxLength={150}
        />

        {renderError("indication")}
    </div>
</div>

                                </div>

                                <Separator />

                                {/* Child information */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 font-semibold">
                                        <Baby className="h-5 w-5 text-primary" />
                                        Child Information
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        <div className="space-y-2">
                                            <Label>Child Weight (kg) *</Label>

                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                max="300"
                                                placeholder="e.g. 12.5"
                                                value={form.childWeightKg}
                                                onChange={(e) =>
                                                    updateField(
                                                        "childWeightKg",
                                                        e.target.value
                                                    )
                                                }
                                            />

                                            {renderError("childWeightKg")}
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Child Age (months) *</Label>

                                            <Input
                                                type="number"
                                                min="0"
                                                max="2400"
                                                step="1"
                                                placeholder="e.g. 24"
                                                value={form.childAgeMonths}
                                                onChange={(e) =>
                                                    updateField(
                                                        "childAgeMonths",
                                                        e.target.value
                                                    )
                                                }
                                            />

                                            {renderError("childAgeMonths")}
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <div className="flex flex-col sm:flex-row gap-3">
                                    <Button
                                        type="submit"
                                        className="flex-1"
                                        disabled={loading || initialLoading}
                                    >
                                        {loading ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Calculating...
                                            </>
                                        ) : (
                                            <>
                                                <Calculator className="mr-2 h-4 w-4" />
                                                Calculate Pediatric Dose
                                            </>
                                        )}
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={resetForm}
                                    >
                                        <RefreshCw className="mr-2 h-4 w-4" />
                                        Reset
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Result */}
                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle2 className="h-5 w-5" />
                                Calculation Result
                            </CardTitle>
                        </CardHeader>

                        <CardContent>
                            {result ? (
                                <div className="space-y-4">
                                    <Alert>
                                        <CheckCircle2 className="h-4 w-4" />
                                        <AlertDescription>
                                            {message || "Calculation completed."}
                                        </AlertDescription>
                                    </Alert>

                                    <div className="rounded-lg bg-muted p-4 space-y-3">
                                        {Object.entries(result).map(
                                            ([key, value]) => (
                                                <div
                                                    key={key}
                                                    className="flex flex-col gap-1 border-b last:border-0 pb-2 last:pb-0"
                                                >
                                                    <span className="text-xs text-muted-foreground">
                                                        {key
                                                            .replace(/([A-Z])/g, " $1")
                                                            .replace(/_/g, " ")
                                                            .replace(/^./, (s) => s.toUpperCase())}
                                                    </span>

                                                    <span className="font-semibold text-sm break-words">
                                                        {value === null ||
                                                        value === undefined
                                                            ? "N/A"
                                                            : typeof value === "object"
                                                            ? JSON.stringify(value)
                                                            : String(value)}
                                                    </span>
                                                </div>
                                            )
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <div className="flex flex-col items-center justify-center text-center py-12">
                                    <Calculator className="h-12 w-12 text-muted-foreground/40 mb-4" />

                                    <h3 className="font-semibold">
                                        No Calculation Yet
                                    </h3>

                                    <p className="text-sm text-muted-foreground mt-2">
                                        Enter medicine and child information
                                        to calculate the dose.
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Recent calculations */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <History className="h-5 w-5" />
                                Recent Calculations
                            </CardTitle>

                            <Button
                                variant="outline"
                                size="sm"
                                onClick={loadData}
                                disabled={initialLoading}
                            >
                                <RefreshCw className="h-4 w-4 mr-2" />
                                Refresh
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        {initialLoading ? (
                            <div className="flex justify-center py-8">
                                <Loader2 className="h-6 w-6 animate-spin" />
                            </div>
                        ) : recent.length ? (
                            <div className="space-y-3">
                                {recent.map((item, index) => (
                                    <div
                                        key={item.id ?? index}
                                        className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-lg border p-4"
                                    >
                                        <div className="space-y-1">
                                            <p className="font-medium">
                                                {item.medicine?.name ??
                                                    item.medicine_name ??
                                                    item.medicineName ??
                                                    "Medicine"}
                                            </p>

                                            <p className="text-sm text-muted-foreground">
                                                Weight: {item.child_weight_kg ??
                                                    item.childWeightKg ?? "-"} kg
                                                {" · "}
                                                Age: {item.child_age_months ??
                                                    item.childAgeMonths ?? "-"} months
                                            </p>

                                             <p className="text-sm text-muted-foreground">
                                                Dose Mg: {item.dose_mg ??
                                                    item.doseMg ?? "-"} mg
                                                {" · "}
                                                Volume: {item.volume_ml ??
                                                    item.volumeMl ?? "-"} ml
                                            </p>

                                            <p className="text-xs text-muted-foreground">
                                                {item.created_at ??
                                                    item.createdAt ?? ""}
                                            </p>
                                        </div>

                                        <Badge variant="secondary">
                                            {item.formulation ?? "Calculation"}
                                        </Badge>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-center text-sm text-muted-foreground py-8">
                                No recent calculations found.
                            </p>
                        )}
                    </CardContent>
                </Card>

                {/* Medical disclaimer */}
                <Alert>
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>
                        Pediatric dose calculations are based on approved
                        rules. Always verify the medicine, concentration,
                        maximum daily dose, contraindications, and clinical
                        suitability before prescribing or administering.
                        This calculator does not replace clinical judgment.
                    </AlertDescription>
                </Alert>
            </div>
        </AppLayout>
    );
}
