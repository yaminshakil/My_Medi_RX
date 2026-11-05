import { useCallback, useEffect, useMemo, useState } from "react";
import axios from "axios";
import { Head } from "@inertiajs/react";
import {
    Plus,
    Pencil,
    Trash2,
    Search,
    RefreshCw,
    Package,
    Loader2,
} from "lucide-react";
import { toast } from "sonner";
import AppLayout from "@/Layouts/app-layout";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Switch } from "@/Components/ui/switch";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

type BillingCycle =
    "monthly" | "quarterly" | "half_yearly" | "yearly" | "custom";
type UserType = "All" | "Patient" | "Doctor" | "Pharmacy" | "Hospital";
type FeatureMap = Record<
    string,
    { enabled: boolean; limit?: number; [key: string]: unknown }
>;
interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: string;
    billing_cycle: BillingCycle;
    duration_days: number;
    user_type: UserType;
    features: FeatureMap | null;
    is_active: boolean;
}
interface ApiErrorResponse {
    message?: string;
    errors?: Record<string, string | string[]>;
}
interface PlanForm {
    name: string;
    slug: string;
    description: string;
    price: string;
    billing_cycle: BillingCycle;
    duration_days: string;
    user_type: UserType;
    featuresText: string;
    is_active: boolean;
}
const emptyForm: PlanForm = {
    name: "",
    slug: "",
    description: "",
    price: "0",
    billing_cycle: "monthly",
    duration_days: "30",
    user_type: "Patient",
    featuresText: JSON.stringify(
        { ai_assistant: { enabled: true, limit: 5 } },
        null,
        2,
    ),
    is_active: true,
};
const endpoint = "/admin/subscription-plans";

function getErrorMessage(error: unknown, fallback: string): string {
    if (!axios.isAxiosError<ApiErrorResponse>(error)) return fallback;

    return error.response?.data?.message ?? fallback;
}

function toForm(plan?: Plan): PlanForm {
    if (!plan) return { ...emptyForm };
    return {
        name: plan.name,
        slug: plan.slug,
        description: plan.description ?? "",
        price: String(plan.price),
        billing_cycle: plan.billing_cycle,
        duration_days: String(plan.duration_days),
        user_type: plan.user_type,
        featuresText: JSON.stringify(plan.features ?? {}, null, 2),
        is_active: plan.is_active,
    };
}

export default function SubscriptionPlansIndex() {
    const [plans, setPlans] = useState<Plan[]>([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [search, setSearch] = useState("");
    const [userType, setUserType] = useState("all");
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<Plan | null>(null);
    const [deleting, setDeleting] = useState<Plan | null>(null);
    const [form, setForm] = useState<PlanForm>({ ...emptyForm });
    const [errors, setErrors] = useState<Record<string, string>>({});

    const loadPlans = useCallback(async () => {
        setLoading(true);
        try {
            const response = await axios.get<{ data: Plan[] }>(endpoint, {
                withCredentials: true,
                headers: { Accept: "application/json" },
            });
            setPlans(response.data.data);
        } catch (error: unknown) {
            toast.error(
                getErrorMessage(error, "Unable to load subscription plans."),
            );
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        void loadPlans();
    }, [loadPlans]);

    const filteredPlans = useMemo(
        () =>
            plans.filter((plan) => {
                const matchesSearch = `${plan.name} ${plan.slug}`
                    .toLowerCase()
                    .includes(search.toLowerCase());
                const matchesType =
                    userType === "all" || plan.user_type === userType;
                return matchesSearch && matchesType;
            }),
        [plans, search, userType],
    );

    const updateField = <K extends keyof PlanForm>(
        key: K,
        value: PlanForm[K],
    ) => setForm((current) => ({ ...current, [key]: value }));
    const openCreate = () => {
        setEditing(null);
        setForm({ ...emptyForm });
        setErrors({});
        setDialogOpen(true);
    };
    const openEdit = (plan: Plan) => {
        setEditing(plan);
        setForm(toForm(plan));
        setErrors({});
        setDialogOpen(true);
    };

    const submit = async (event: React.FormEvent) => {
        event.preventDefault();
        setErrors({});
        let features: FeatureMap;
        try {
            features = JSON.parse(form.featuresText || "{}");
            if (
                !features ||
                Array.isArray(features) ||
                typeof features !== "object"
            )
                throw new Error();
        } catch {
            setErrors({ features: "Features must be a valid JSON object." });
            return;
        }
        const payload = {
            name: form.name.trim(),
            slug: form.slug.trim() || undefined,
            description: form.description || null,
            price: Number(form.price),
            billing_cycle: form.billing_cycle,
            duration_days: Number(form.duration_days),
            user_type: form.user_type,
            features,
            is_active: form.is_active,
        };
        setSaving(true);
        try {
            if (editing) {
                await axios.put(`${endpoint}/${editing.id}`, payload, {
                    withCredentials: true,
                    headers: { Accept: "application/json" },
                });
                toast.success("Subscription plan updated.");
            } else {
                await axios.post(endpoint, payload, {
                    withCredentials: true,
                    headers: { Accept: "application/json" },
                });
                toast.success("Subscription plan created.");
            }
            setDialogOpen(false);
            await loadPlans();
        } catch (error: unknown) {
            const responseErrors = axios.isAxiosError<ApiErrorResponse>(error)
                ? error.response?.data?.errors
                : undefined;
            if (responseErrors)
                setErrors(
                    Object.fromEntries(
                        Object.entries(responseErrors).map(([key, value]) => [
                            key,
                            Array.isArray(value) ? String(value[0]) : value,
                        ]),
                    ),
                );
            else toast.error(getErrorMessage(error, "Unable to save plan."));
        } finally {
            setSaving(false);
        }
    };

    const removePlan = async () => {
        if (!deleting) return;
        try {
            const response = await axios.delete(`${endpoint}/${deleting.id}`, {
                withCredentials: true,
                headers: { Accept: "application/json" },
            });
            toast.success(response.data?.message ?? "Plan deleted.");
            setDeleting(null);
            await loadPlans();
        } catch (error: unknown) {
            toast.error(getErrorMessage(error, "Unable to delete plan."));
        }
    };

    const toggleActive = async (plan: Plan, active: boolean) => {
        try {
            await axios.patch(
                `${endpoint}/${plan.id}`,
                { ...toPayload(plan), is_active: active },
                {
                    withCredentials: true,
                    headers: { Accept: "application/json" },
                },
            );
            toast.success(active ? "Plan activated." : "Plan deactivated.");
            await loadPlans();
        } catch (error: unknown) {
            toast.error(
                getErrorMessage(error, "Unable to update plan status."),
            );
        }
    };

    const toPayload = (plan: Plan) => ({
        name: plan.name,
        slug: plan.slug,
        description: plan.description,
        price: plan.price,
        billing_cycle: plan.billing_cycle,
        duration_days: plan.duration_days,
        user_type: plan.user_type,
        features: plan.features ?? {},
        is_active: plan.is_active,
    });
    const currency = (amount: string) =>
        new Intl.NumberFormat("en-BD", {
            style: "currency",
            currency: "BDT",
            maximumFractionDigits: 2,
        }).format(Number(amount));

    return (
        <AppLayout>
            <>
                <Head title="Subscription Plans" />
                <div className="space-y-6 p-4 md:p-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Subscription Plans
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Create and manage plans for Patients, Doctors,
                                Pharmacies and Hospitals.
                            </p>
                        </div>
                        <Button onClick={openCreate}>
                            <Plus className="mr-2 h-4 w-4" /> Create Plan
                        </Button>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Total plans
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {plans.length}
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Active plans
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {plans.filter((p) => p.is_active).length}
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    User types covered
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {
                                        new Set(plans.map((p) => p.user_type))
                                            .size
                                    }
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                className="pl-9"
                                placeholder="Search plan name or slug..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                        <Select value={userType} onValueChange={setUserType}>
                            <SelectTrigger className="w-full sm:w-48">
                                <SelectValue placeholder="Filter user type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All user types
                                </SelectItem>
                                {(
                                    [
                                        "Patient",
                                        "Doctor",
                                        "Pharmacy",
                                        "Hospital",
                                        "All",
                                    ] as const
                                ).map((type) => (
                                    <SelectItem key={type} value={type}>
                                        {type}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Button
                            variant="outline"
                            onClick={() => void loadPlans()}
                            disabled={loading}
                        >
                            <RefreshCw
                                className={`mr-2 h-4 w-4 ${loading ? "animate-spin" : ""}`}
                            />{" "}
                            Refresh
                        </Button>
                    </div>
                    <Card>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Plan</TableHead>
                                        <TableHead>Audience</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Duration</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {loading ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="h-28 text-center"
                                            >
                                                <Loader2 className="mr-2 inline h-4 w-4 animate-spin" />{" "}
                                                Loading plans...
                                            </TableCell>
                                        </TableRow>
                                    ) : filteredPlans.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="h-28 text-center text-muted-foreground"
                                            >
                                                <Package className="mr-2 inline h-4 w-4" />{" "}
                                                No subscription plans found.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        filteredPlans.map((plan) => (
                                            <TableRow key={plan.id}>
                                                <TableCell>
                                                    <div className="font-medium">
                                                        {plan.name}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {plan.slug}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">
                                                        {plan.user_type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    {currency(plan.price)}
                                                    <div className="text-xs font-normal text-muted-foreground">
                                                        {plan.billing_cycle.replace(
                                                            "_",
                                                            " ",
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {plan.duration_days} days
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <Badge
                                                            variant={
                                                                plan.is_active
                                                                    ? "default"
                                                                    : "secondary"
                                                            }
                                                        >
                                                            {plan.is_active
                                                                ? "Active"
                                                                : "Inactive"}
                                                        </Badge>
                                                        <Switch
                                                            checked={
                                                                plan.is_active
                                                            }
                                                            onCheckedChange={(
                                                                value,
                                                            ) =>
                                                                void toggleActive(
                                                                    plan,
                                                                    value,
                                                                )
                                                            }
                                                            aria-label={`Toggle ${plan.name}`}
                                                        />
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() =>
                                                                openEdit(plan)
                                                            }
                                                        >
                                                            <Pencil className="mr-1 h-3.5 w-3.5" />{" "}
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() =>
                                                                setDeleting(
                                                                    plan,
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="mr-1 h-3.5 w-3.5" />{" "}
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                    <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>
                                {editing
                                    ? "Edit subscription plan"
                                    : "Create subscription plan"}
                            </DialogTitle>
                            <DialogDescription>
                                Configure pricing, audience, billing duration
                                and feature limits.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="plan-name">
                                        Plan name *
                                    </Label>
                                    <Input
                                        id="plan-name"
                                        value={form.name}
                                        onChange={(e) =>
                                            updateField("name", e.target.value)
                                        }
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-xs text-destructive">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="plan-slug">
                                        Slug (optional)
                                    </Label>
                                    <Input
                                        id="plan-slug"
                                        value={form.slug}
                                        onChange={(e) =>
                                            updateField("slug", e.target.value)
                                        }
                                        placeholder="auto-generated-from-name"
                                    />
                                    {errors.slug && (
                                        <p className="text-xs text-destructive">
                                            {errors.slug}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="plan-price">
                                        Price (BDT) *
                                    </Label>
                                    <Input
                                        id="plan-price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={form.price}
                                        onChange={(e) =>
                                            updateField("price", e.target.value)
                                        }
                                        required
                                    />
                                    {errors.price && (
                                        <p className="text-xs text-destructive">
                                            {errors.price}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label>User type *</Label>
                                    <Select
                                        value={form.user_type}
                                        onValueChange={(value) =>
                                            updateField(
                                                "user_type",
                                                value as UserType,
                                            )
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {(
                                                [
                                                    "All",
                                                    "Patient",
                                                    "Doctor",
                                                    "Pharmacy",
                                                    "Hospital",
                                                ] as const
                                            ).map((type) => (
                                                <SelectItem
                                                    key={type}
                                                    value={type}
                                                >
                                                    {type}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.user_type && (
                                        <p className="text-xs text-destructive">
                                            {errors.user_type}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label>Billing cycle *</Label>
                                    <Select
                                        value={form.billing_cycle}
                                        onValueChange={(value) => {
                                            const cycle = value as BillingCycle;
                                            updateField("billing_cycle", cycle);
                                            const days: Record<
                                                BillingCycle,
                                                string
                                            > = {
                                                monthly: "30",
                                                quarterly: "90",
                                                half_yearly: "180",
                                                yearly: "365",
                                                custom: form.duration_days,
                                            };
                                            updateField(
                                                "duration_days",
                                                days[cycle],
                                            );
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="monthly">
                                                Monthly
                                            </SelectItem>
                                            <SelectItem value="quarterly">
                                                Quarterly
                                            </SelectItem>
                                            <SelectItem value="half_yearly">
                                                Half-yearly
                                            </SelectItem>
                                            <SelectItem value="yearly">
                                                Yearly
                                            </SelectItem>
                                            <SelectItem value="custom">
                                                Custom
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.billing_cycle && (
                                        <p className="text-xs text-destructive">
                                            {errors.billing_cycle}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label>Duration (days) *</Label>
                                    <Input
                                        type="number"
                                        min="1"
                                        max="3650"
                                        value={form.duration_days}
                                        onChange={(e) =>
                                            updateField(
                                                "duration_days",
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {errors.duration_days && (
                                        <p className="text-xs text-destructive">
                                            {errors.duration_days}
                                        </p>
                                    )}
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label>Description</Label>
                                <Textarea
                                    value={form.description}
                                    onChange={(e) =>
                                        updateField(
                                            "description",
                                            e.target.value,
                                        )
                                    }
                                    rows={2}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Features and limits (JSON)</Label>
                                <Textarea
                                    className="min-h-44 font-mono text-xs"
                                    value={form.featuresText}
                                    onChange={(e) =>
                                        updateField(
                                            "featuresText",
                                            e.target.value,
                                        )
                                    }
                                    spellCheck={false}
                                />
                                {errors.features && (
                                    <p className="text-xs text-destructive">
                                        {errors.features}
                                    </p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    Use enabled: true/false and limit: -1 for
                                    unlimited.
                                </p>
                            </div>
                            <div className="flex items-center justify-between rounded-lg border p-3">
                                <div>
                                    <Label>Active plan</Label>
                                    <p className="text-xs text-muted-foreground">
                                        Inactive plans are hidden from
                                        customers.
                                    </p>
                                </div>
                                <Switch
                                    checked={form.is_active}
                                    onCheckedChange={(value) =>
                                        updateField("is_active", value)
                                    }
                                />
                            </div>
                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setDialogOpen(false)}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={saving}>
                                    {saving && (
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    )}
                                    {editing ? "Save changes" : "Create plan"}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <AlertDialog
                    open={!!deleting}
                    onOpenChange={(open) => !open && setDeleting(null)}
                >
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Delete subscription plan?
                            </AlertDialogTitle>
                            <AlertDialogDescription>
                                {deleting?.name} will be permanently deleted
                                only if it has no subscriptions. If it has
                                subscription history, it will be deactivated to
                                preserve records.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() => void removePlan()}
                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            >
                                Delete / Deactivate
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </>
        </AppLayout>
    );
}
