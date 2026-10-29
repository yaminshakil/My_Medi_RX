import { FormEvent, useState } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import {
    AlertTriangle,
    ArrowDown,
    ArrowUp,
    Boxes,
    CalendarClock,
    ChevronLeft,
    ChevronRight,
    Package,
    Pencil,
    Plus,
    Search,
    Trash2,
} from "lucide-react";

import { Alert, AlertDescription } from "@/Components/ui/alert";
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
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent } from "@/Components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import AppLayout from "@/Layouts/app-layout";

type Pharmacy = {
    id: number;
    name: string;
};

type InventoryItem = {
    id: number;
    pharmacy_id: number;
    medicine_name: string;
    generic_name: string | null;
    strength: string | null;
    stock_quantity: number;
    reorder_level: number;
    expiry_date: string | null;
    pharmacy: Pharmacy;
};

type InventoryForm = {
    pharmacy_id: string;
    medicine_name: string;
    generic_name: string;
    strength: string;
    stock_quantity: string;
    reorder_level: string;
    expiry_date: string;
};

type StockForm = {
    direction: "increase" | "decrease";
    quantity: string;
};

interface InventoryIndexProps {
    inventory: {
        data: InventoryItem[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
    pharmacies: Pharmacy[];
    stats: {
        totalItems: number;
        totalUnits: number;
        lowStockItems: number;
        outOfStockItems: number;
        expiringSoonItems: number;
        expiredItems: number;
    };
    filters: {
        search: string;
        pharmacy_id: string;
        stock_status: string;
        per_page: number;
    };
}

const emptyForm: InventoryForm = {
    pharmacy_id: "",
    medicine_name: "",
    generic_name: "",
    strength: "",
    stock_quantity: "0",
    reorder_level: "0",
    expiry_date: "",
};

const statCards = [
    { key: "totalItems", label: "Inventory items", icon: Boxes },
    { key: "totalUnits", label: "Units in stock", icon: Package },
    { key: "lowStockItems", label: "At or below reorder", icon: AlertTriangle },
    { key: "outOfStockItems", label: "Out of stock", icon: ArrowDown },
    {
        key: "expiringSoonItems",
        label: "Expiring in 30 days",
        icon: CalendarClock,
    },
    { key: "expiredItems", label: "Expired items", icon: CalendarClock },
] as const;

function stockBadge(item: InventoryItem) {
    if (item.stock_quantity === 0) {
        return <Badge variant="destructive">Out of stock</Badge>;
    }

    if (item.stock_quantity <= item.reorder_level) {
        return <Badge variant="secondary">Low stock</Badge>;
    }

    return <Badge variant="outline">In stock</Badge>;
}

export default function Index({
    inventory,
    pharmacies,
    stats,
    filters,
}: InventoryIndexProps) {
    const [search, setSearch] = useState(filters.search);
    const [pharmacyId, setPharmacyId] = useState(filters.pharmacy_id);
    const [stockStatus, setStockStatus] = useState(filters.stock_status);
    const [itemDialogOpen, setItemDialogOpen] = useState(false);
    const [editingItem, setEditingItem] = useState<InventoryItem | null>(null);
    const [adjustingItem, setAdjustingItem] = useState<InventoryItem | null>(
        null,
    );
    const [deletingItem, setDeletingItem] = useState<InventoryItem | null>(
        null,
    );
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const form = useForm<InventoryForm>(emptyForm);
    const stockForm = useForm<StockForm>({
        direction: "increase",
        quantity: "",
    });

    function applyFilters(page = 1) {
        router.get(
            route("admin.inventory.index"),
            {
                search: search || undefined,
                pharmacy_id: pharmacyId || undefined,
                stock_status: stockStatus,
                per_page: filters.per_page,
                page,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function openCreateDialog() {
        setEditingItem(null);
        form.clearErrors();
        form.setData({
            ...emptyForm,
            pharmacy_id: pharmacies[0] ? String(pharmacies[0].id) : "",
        });
        setItemDialogOpen(true);
    }

    function openEditDialog(item: InventoryItem) {
        setEditingItem(item);
        form.clearErrors();
        form.setData({
            pharmacy_id: String(item.pharmacy_id),
            medicine_name: item.medicine_name,
            generic_name: item.generic_name ?? "",
            strength: item.strength ?? "",
            stock_quantity: String(item.stock_quantity),
            reorder_level: String(item.reorder_level),
            expiry_date: item.expiry_date ?? "",
        });
        setItemDialogOpen(true);
    }

    function submitItem(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setItemDialogOpen(false);
                setEditingItem(null);
                form.reset();
            },
        };

        if (editingItem) {
            form.transform((data) => ({
                medicine_name: data.medicine_name,
                generic_name: data.generic_name,
                strength: data.strength,
                reorder_level: data.reorder_level,
                expiry_date: data.expiry_date,
            }));
            form.put(route("admin.inventory.update", editingItem.id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });
            return;
        }

        form.transform((data) => data);
        form.post(route("admin.inventory.store"), options);
    }

    function openStockDialog(item: InventoryItem) {
        setAdjustingItem(item);
        stockForm.clearErrors();
        stockForm.setData({ direction: "increase", quantity: "" });
    }

    function submitStockAdjustment(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!adjustingItem) {
            return;
        }

        stockForm.patch(route("admin.inventory.stock", adjustingItem.id), {
            preserveScroll: true,
            onSuccess: () => setAdjustingItem(null),
        });
    }

    function deleteItem() {
        if (!deletingItem) {
            return;
        }

        router.delete(route("admin.inventory.destroy", deletingItem.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingItem(null);
                setDeleteError(null);
            },
            onError: (errors) =>
                setDeleteError(
                    errors.inventoryItem ??
                        "The inventory item could not be deleted.",
                ),
        });
    }

    function openDeleteDialog(item: InventoryItem) {
        setDeleteError(null);
        setDeletingItem(item);
    }

    function itemFieldError(field: keyof InventoryForm) {
        const error = form.errors[field];

        return error ? (
            <p className="text-sm text-destructive">{error}</p>
        ) : null;
    }

    function formatDate(date: string | null) {
        return date ? new Date(`${date}T00:00:00`).toLocaleDateString() : "—";
    }

    const hasPharmacies = pharmacies.length > 0;

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: "Inventory & Stock",
                    href: route("admin.inventory.index"),
                },
            ]}
        >
            <Head title="Inventory & Stock" />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-sm font-medium text-primary">
                            Pharmacy operations
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                            Inventory &amp; stock
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Monitor medicine levels across pharmacies and adjust
                            stock safely.
                        </p>
                    </div>
                    <Button
                        onClick={openCreateDialog}
                        disabled={!hasPharmacies}
                    >
                        <Plus className="size-4" />
                        Add inventory item
                    </Button>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    {statCards.map(({ key, label, icon: Icon }) => (
                        <Card key={key}>
                            <CardContent className="flex items-center justify-between gap-3 p-5">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        {label}
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold">
                                        {stats[key].toLocaleString()}
                                    </p>
                                </div>
                                <span className="rounded-lg bg-primary/10 p-3 text-primary">
                                    <Icon className="size-5" />
                                </span>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {!hasPharmacies && (
                    <Alert>
                        <AlertTriangle className="size-4" />
                        <AlertDescription>
                            Create a pharmacy before adding inventory.
                        </AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardContent className="flex flex-col gap-4 p-4">
                        <form
                            className="flex flex-col gap-3 lg:flex-row"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    className="pl-9"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Search medicine or pharmacy"
                                />
                            </div>
                            <select
                                aria-label="Filter by pharmacy"
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                                value={pharmacyId}
                                onChange={(event) => {
                                    setPharmacyId(event.target.value);
                                    router.get(
                                        route("admin.inventory.index"),
                                        {
                                            search: search || undefined,
                                            pharmacy_id:
                                                event.target.value || undefined,
                                            stock_status: stockStatus,
                                            per_page: filters.per_page,
                                        },
                                        {
                                            preserveState: true,
                                            preserveScroll: true,
                                            replace: true,
                                        },
                                    );
                                }}
                            >
                                <option value="">All pharmacies</option>
                                {pharmacies.map((pharmacy) => (
                                    <option
                                        key={pharmacy.id}
                                        value={pharmacy.id}
                                    >
                                        {pharmacy.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                aria-label="Filter by stock or expiry"
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                                value={stockStatus}
                                onChange={(event) => {
                                    setStockStatus(event.target.value);
                                    router.get(
                                        route("admin.inventory.index"),
                                        {
                                            search: search || undefined,
                                            pharmacy_id:
                                                pharmacyId || undefined,
                                            stock_status: event.target.value,
                                            per_page: filters.per_page,
                                        },
                                        {
                                            preserveState: true,
                                            preserveScroll: true,
                                            replace: true,
                                        },
                                    );
                                }}
                            >
                                <option value="all">All stock</option>
                                <option value="low">Low stock</option>
                                <option value="out">Out of stock</option>
                                <option value="expiring">
                                    Expiring in 30 days
                                </option>
                                <option value="expired">Expired</option>
                            </select>
                            <Button type="submit" variant="secondary">
                                Search
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setSearch("");
                                    setPharmacyId("");
                                    setStockStatus("all");
                                    router.get(
                                        route("admin.inventory.index"),
                                        { per_page: filters.per_page },
                                        {
                                            preserveState: true,
                                            preserveScroll: true,
                                            replace: true,
                                        },
                                    );
                                }}
                            >
                                Reset
                            </Button>
                        </form>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Medicine</TableHead>
                                        <TableHead>Pharmacy</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Reorder level</TableHead>
                                        <TableHead>Expiry</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {inventory.data.length > 0 ? (
                                        inventory.data.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    <p className="font-medium">
                                                        {item.medicine_name}
                                                    </p>
                                                    {(item.generic_name ||
                                                        item.strength) && (
                                                        <p className="mt-1 text-xs text-muted-foreground">
                                                            {[
                                                                item.generic_name,
                                                                item.strength,
                                                            ]
                                                                .filter(Boolean)
                                                                .join(" · ")}
                                                        </p>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {item.pharmacy.name}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex flex-col items-start gap-1">
                                                        <span className="font-medium">
                                                            {item.stock_quantity.toLocaleString()}
                                                        </span>
                                                        {stockBadge(item)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {item.reorder_level.toLocaleString()}
                                                </TableCell>
                                                <TableCell>
                                                    {formatDate(
                                                        item.expiry_date,
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() =>
                                                                openStockDialog(
                                                                    item,
                                                                )
                                                            }
                                                        >
                                                            <ArrowUp className="size-4" />
                                                            Adjust
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={`Edit ${item.medicine_name}`}
                                                            onClick={() =>
                                                                openEditDialog(
                                                                    item,
                                                                )
                                                            }
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={`Delete ${item.medicine_name}`}
                                                            onClick={() =>
                                                                openDeleteDialog(
                                                                    item,
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="size-4 text-destructive" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="h-24 text-center text-muted-foreground"
                                            >
                                                No inventory items match these
                                                filters.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                            <p>
                                Showing {inventory.from ?? 0}–
                                {inventory.to ?? 0} of {inventory.total} items
                            </p>
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    disabled={inventory.current_page <= 1}
                                    onClick={() =>
                                        applyFilters(inventory.current_page - 1)
                                    }
                                >
                                    <ChevronLeft className="size-4" />
                                    Previous
                                </Button>
                                <span>
                                    Page {inventory.current_page} of{" "}
                                    {inventory.last_page}
                                </span>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    disabled={
                                        inventory.current_page >=
                                        inventory.last_page
                                    }
                                    onClick={() =>
                                        applyFilters(inventory.current_page + 1)
                                    }
                                >
                                    Next
                                    <ChevronRight className="size-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={itemDialogOpen} onOpenChange={setItemDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editingItem
                                ? "Edit inventory item"
                                : "Add inventory item"}
                        </DialogTitle>
                        <DialogDescription>
                            {editingItem
                                ? "Update medicine details and reorder information. Use stock adjustment to change quantity."
                                : "Add a medicine to a pharmacy and enter its current quantity."}
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitItem} className="grid gap-4">
                        {!editingItem && (
                            <div className="grid gap-2">
                                <Label htmlFor="pharmacy_id">Pharmacy</Label>
                                <select
                                    id="pharmacy_id"
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    value={form.data.pharmacy_id}
                                    onChange={(event) =>
                                        form.setData(
                                            "pharmacy_id",
                                            event.target.value,
                                        )
                                    }
                                    required
                                >
                                    <option value="">Select a pharmacy</option>
                                    {pharmacies.map((pharmacy) => (
                                        <option
                                            key={pharmacy.id}
                                            value={pharmacy.id}
                                        >
                                            {pharmacy.name}
                                        </option>
                                    ))}
                                </select>
                                {itemFieldError("pharmacy_id")}
                            </div>
                        )}
                        <div className="grid gap-2">
                            <Label htmlFor="medicine_name">Medicine name</Label>
                            <Input
                                id="medicine_name"
                                value={form.data.medicine_name}
                                onChange={(event) =>
                                    form.setData(
                                        "medicine_name",
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            {itemFieldError("medicine_name")}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="generic_name">
                                    Generic name
                                </Label>
                                <Input
                                    id="generic_name"
                                    value={form.data.generic_name}
                                    onChange={(event) =>
                                        form.setData(
                                            "generic_name",
                                            event.target.value,
                                        )
                                    }
                                />
                                {itemFieldError("generic_name")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="strength">Strength</Label>
                                <Input
                                    id="strength"
                                    value={form.data.strength}
                                    onChange={(event) =>
                                        form.setData(
                                            "strength",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="e.g. 500 mg"
                                />
                                {itemFieldError("strength")}
                            </div>
                        </div>
                        {!editingItem && (
                            <div className="grid gap-2">
                                <Label htmlFor="stock_quantity">
                                    Initial stock quantity
                                </Label>
                                <Input
                                    id="stock_quantity"
                                    type="number"
                                    min="0"
                                    max="10000000"
                                    value={form.data.stock_quantity}
                                    onChange={(event) =>
                                        form.setData(
                                            "stock_quantity",
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {itemFieldError("stock_quantity")}
                            </div>
                        )}
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="reorder_level">
                                    Reorder level
                                </Label>
                                <Input
                                    id="reorder_level"
                                    type="number"
                                    min="0"
                                    max="10000000"
                                    value={form.data.reorder_level}
                                    onChange={(event) =>
                                        form.setData(
                                            "reorder_level",
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {itemFieldError("reorder_level")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="expiry_date">Expiry date</Label>
                                <Input
                                    id="expiry_date"
                                    type="date"
                                    value={form.data.expiry_date}
                                    onChange={(event) =>
                                        form.setData(
                                            "expiry_date",
                                            event.target.value,
                                        )
                                    }
                                />
                                {itemFieldError("expiry_date")}
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setItemDialogOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing
                                    ? "Saving..."
                                    : editingItem
                                      ? "Save changes"
                                      : "Add item"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={adjustingItem !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setAdjustingItem(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Adjust stock</DialogTitle>
                        <DialogDescription>
                            {adjustingItem
                                ? `${adjustingItem.medicine_name} currently has ${adjustingItem.stock_quantity} units.`
                                : ""}
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={submitStockAdjustment}
                        className="grid gap-4"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="direction">Adjustment</Label>
                            <select
                                id="direction"
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                                value={stockForm.data.direction}
                                onChange={(event) => {
                                    if (
                                        event.target.value === "increase" ||
                                        event.target.value === "decrease"
                                    ) {
                                        stockForm.setData(
                                            "direction",
                                            event.target.value,
                                        );
                                    }
                                }}
                            >
                                <option value="increase">Add stock</option>
                                <option value="decrease">Remove stock</option>
                            </select>
                            {stockForm.errors.direction && (
                                <p className="text-sm text-destructive">
                                    {stockForm.errors.direction}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="adjustment_quantity">
                                Quantity
                            </Label>
                            <Input
                                id="adjustment_quantity"
                                type="number"
                                min="1"
                                max="10000000"
                                value={stockForm.data.quantity}
                                onChange={(event) =>
                                    stockForm.setData(
                                        "quantity",
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            {stockForm.errors.quantity && (
                                <p className="text-sm text-destructive">
                                    {stockForm.errors.quantity}
                                </p>
                            )}
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setAdjustingItem(null)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={stockForm.processing}
                            >
                                {stockForm.processing
                                    ? "Updating..."
                                    : "Update stock"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <AlertDialog
                open={deletingItem !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeletingItem(null);
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Delete inventory item?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {deletingItem
                                ? `Delete ${deletingItem.medicine_name} from ${deletingItem.pharmacy.name}? Items with dispense history cannot be deleted.`
                                : ""}
                            {deleteError && (
                                <span className="mt-2 block text-destructive">
                                    {deleteError}
                                </span>
                            )}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            className="bg-destructive text-white hover:bg-destructive/90"
                            onClick={deleteItem}
                        >
                            Delete item
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
