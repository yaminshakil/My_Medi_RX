
import { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    Plus,
    Search,
    Pencil,
    Trash2,
    Syringe,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Switch } from '@/Components/ui/switch';
import { Badge } from '@/Components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import AppLayout from '@/Layouts/app-layout';

interface Vaccine {
    id: number;
    uuid: string;
    name: string;
    code: string | null;
    manufacturer_id: string | null;
    description: string | null;
    is_active: boolean;
}

interface Manufacturers {
    id: number;
    name: string;
}

interface PaginatedVaccines {
    data: Vaccine[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    vaccines: PaginatedVaccines;
    manufacturers: Manufacturers;
    filters: {
        search: string;
        status: string;
    };
}

interface VaccineForm {
    name: string;
    code: string;
    manufacturer_id: string;
    description: string;
    is_active: boolean;
}

const emptyForm: VaccineForm = {
    name: '',
    code: '',
    manufacturer_id: '',
    description: '',
    is_active: true,
};

export default function Index({ vaccines, filters, manufacturers }: Props) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editingVaccine, setEditingVaccine] = useState<Vaccine | null>(null);
    const [deletingVaccine, setDeletingVaccine] = useState<Vaccine | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');

    const form = useForm<VaccineForm>(emptyForm);

    function applyFilters(nextSearch = search, nextStatus = status) {
        router.get(
            route('admin.vaccines.index'),
            {
                search: nextSearch || undefined,
                status: nextStatus,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function openCreateDialog() {
        setEditingVaccine(null);
        form.clearErrors();
        form.setData({ ...emptyForm });
        setDialogOpen(true);
    }

    function openEditDialog(vaccine: Vaccine) {
        setEditingVaccine(vaccine);
        form.clearErrors();
        form.setData({
            name: vaccine.name,
            code: vaccine.code ?? '',
            manufacturer_id: vaccine.manufacturer_id ?? '',
            description: vaccine.description ?? '',
            is_active: Boolean(vaccine.is_active),
        });
        setDialogOpen(true);
    }

    function submitForm(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setDialogOpen(false);
                setEditingVaccine(null);
                form.reset();
            },
        };

        if (editingVaccine) {
            form.put(
                route('admin.vaccines.update', editingVaccine.uuid),
                options,
            );
        } else {
            form.post(route('admin.vaccines.store'), options);
        }
    }

    function deleteVaccine() {
        if (!deletingVaccine) return;

        router.delete(
            route('admin.vaccines.destroy', deletingVaccine.uuid),
            {
                preserveScroll: true,
                onSuccess: () => setDeletingVaccine(null),
            },
        );
    }

    function changePage(page: number) {
        router.get(
            route('admin.vaccines.index'),
            {
                search: filters.search || undefined,
                status: filters.status || 'all',
                page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    const [loadingManufacturers, setLoadingManufacturers] = useState(false);

    /**
  * Manufacturer change
  */
    const handleManufacturerChange = (value: string) => {
        const manufacturerId = value;

        form.setData('manufacturer_id', manufacturerId);
    };

    return (
        <AppLayout>
            <Head title="Vaccine Management" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <Syringe className="h-6 w-6 text-primary" />
                            <h1 className="text-2xl font-bold tracking-tight">
                                Vaccine Management
                            </h1>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Manage vaccine information for patient immunization.
                        </p>
                    </div>

                    <Button onClick={openCreateDialog}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Vaccine
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Vaccines</CardTitle>
                        <CardDescription>
                            {vaccines.total} vaccine
                            {vaccines.total === 1 ? '' : 's'} in the catalog
                        </CardDescription>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <form
                                className="relative flex-1"
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    applyFilters(search, status);
                                }}
                            >
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Search name, code, manufacturer..."
                                    className="pl-9"
                                />
                            </form>

                            <Select
                                value={status}
                                onValueChange={(value) => {
                                    setStatus(value);
                                    applyFilters(search, value);
                                }}
                            >
                                <SelectTrigger className="w-full sm:w-[180px]">
                                    <SelectValue placeholder="Filter status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All vaccines</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button
                                variant="outline"
                                onClick={() => {
                                    setSearch('');
                                    setStatus('all');
                                    applyFilters('', 'all');
                                }}
                            >
                                Reset
                            </Button>
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Vaccine</TableHead>
                                        <TableHead>Code</TableHead>
                                        <TableHead>Manufacturer ID</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    {vaccines.data.length > 0 ? (
                                        vaccines.data.map((vaccine) => (
                                            <TableRow key={vaccine.uuid}>
                                                <TableCell>
                                                    <div className="font-medium">
                                                        {vaccine.name}
                                                    </div>
                                                    {vaccine.description && (
                                                        <p className="mt-1 max-w-xs truncate text-xs text-muted-foreground">
                                                            {vaccine.description}
                                                        </p>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    {vaccine.code || '—'}
                                                </TableCell>

                                                <TableCell>
                                                    {vaccine.manufacturer_id || '—'}
                                                </TableCell>

                                                <TableCell>
                                                    <Badge
                                                        variant={
                                                            vaccine.is_active
                                                                ? 'default'
                                                                : 'secondary'
                                                        }
                                                    >
                                                        {vaccine.is_active
                                                            ? 'Active'
                                                            : 'Inactive'}
                                                    </Badge>
                                                </TableCell>

                                                <TableCell>
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            size="icon"
                                                            variant="outline"
                                                            title="Edit vaccine"
                                                            onClick={() =>
                                                                openEditDialog(vaccine)
                                                            }
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>

                                                        <Button
                                                            size="icon"
                                                            variant="destructive"
                                                            title="Delete vaccine"
                                                            onClick={() =>
                                                                setDeletingVaccine(vaccine)
                                                            }
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={5}
                                                className="h-32 text-center text-muted-foreground"
                                            >
                                                No vaccines found. Add a vaccine
                                                or change your filters.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-muted-foreground">
                                Page {vaccines.current_page} of {vaccines.last_page}
                            </p>

                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={vaccines.current_page <= 1}
                                    onClick={() =>
                                        changePage(vaccines.current_page - 1)
                                    }
                                >
                                    <ChevronLeft className="mr-1 h-4 w-4" />
                                    Previous
                                </Button>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={
                                        vaccines.current_page >= vaccines.last_page
                                    }
                                    onClick={() =>
                                        changePage(vaccines.current_page + 1)
                                    }
                                >
                                    Next
                                    <ChevronRight className="ml-1 h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>
                            {editingVaccine ? 'Edit Vaccine' : 'Add Vaccine'}
                        </DialogTitle>
                        <DialogDescription>
                            Enter the vaccine catalog information below.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submitForm} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Vaccine Name *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(event) =>
                                    form.setData('name', event.target.value)
                                }
                                placeholder="e.g. Hepatitis B"
                                required
                            />
                            {form.errors.name && (
                                <p className="text-sm text-destructive">
                                    {form.errors.name}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="code">Vaccine Code</Label>
                                <Input
                                    id="code"
                                    value={form.data.code}
                                    onChange={(event) =>
                                        form.setData('code', event.target.value)
                                    }
                                    placeholder="e.g. HEPB"
                                />
                                {form.errors.code && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.code}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="manufacturer_id">Manufacturer</Label>

                                <Select
                                    value={form.data.manufacturer_id?.toString() ?? ''}
                                    onValueChange={handleManufacturerChange}
                                >
                                    <SelectTrigger id="manufacturer_id" className="w-full">
                                        <SelectValue
                                            placeholder={
                                                loadingManufacturers
                                                    ? 'Loading Manufacturer...'
                                                    : 'Select Manufacturer'
                                            }
                                        />
                                    </SelectTrigger>

                                    <SelectContent>
                                        {manufacturers.map((manufacturer) => (
                                            <SelectItem
                                                key={manufacturer.id}
                                                value={manufacturer.id.toString()}
                                            >
                                                {manufacturer.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {form.errors.manufacturer_id && (
                                    <p className="text-sm font-medium text-destructive">
                                        {form.errors.manufacturer_id}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <Textarea
                                id="description"
                                value={form.data.description}
                                onChange={(event) =>
                                    form.setData('description', event.target.value)
                                }
                                placeholder="Vaccine description..."
                                rows={3}
                            />
                            {form.errors.description && (
                                <p className="text-sm text-destructive">
                                    {form.errors.description}
                                </p>
                            )}
                        </div>

                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <div className="space-y-1">
                                <Label htmlFor="is_active">Active Status</Label>
                                <p className="text-xs text-muted-foreground">
                                    Allow this vaccine to be selected for schedules.
                                </p>
                            </div>

                            <Switch
                                id="is_active"
                                checked={form.data.is_active}
                                onCheckedChange={(checked) =>
                                    form.setData('is_active', checked)
                                }
                            />
                        </div>
                        {form.errors.is_active && (
                            <p className="text-sm text-destructive">
                                {form.errors.is_active}
                            </p>
                        )}

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setDialogOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing
                                    ? 'Saving...'
                                    : editingVaccine
                                        ? 'Update Vaccine'
                                        : 'Create Vaccine'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <AlertDialog
                open={Boolean(deletingVaccine)}
                onOpenChange={(open) => {
                    if (!open) setDeletingVaccine(null);
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete vaccine?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This will soft-delete{' '}
                            <strong>{deletingVaccine?.name}</strong>. It will no
                            longer appear in the normal vaccine list.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={deleteVaccine}>
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
