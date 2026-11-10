import { FormEvent, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import type { Paginated, VaccinationSchedule, VaccineOption } from '@/Components/vaccination/types';

type Props = {
    schedules: Paginated<VaccinationSchedule>;
    vaccines: VaccineOption[];
    filters: { search?: string; vaccine_id?: string; is_active?: string; per_page?: number };
};

export default function Index({ schedules, vaccines, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [deleteId, setDeleteId] = useState<number | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        router.get(route('vaccination-schedules.index'), {
            search,
            vaccine_id: filters.vaccine_id ?? '',
            is_active: filters.is_active ?? '',
            per_page: filters.per_page ?? 10,
        }, { preserveState: true, replace: true });
    };

    const changeFilter = (key: string, value: string) => {
        router.get(route('vaccination-schedules.index'), {
            search,
            vaccine_id: key === 'vaccine_id' ? value : filters.vaccine_id ?? '',
            is_active: key === 'is_active' ? value : filters.is_active ?? '',
            per_page: filters.per_page ?? 10,
        }, { preserveState: true, replace: true });
    };

    const destroy = () => {
        if (!deleteId) return;
        router.delete(route('vaccination-schedules.destroy', deleteId), {
            preserveScroll: true,
            onSuccess: () => setDeleteId(null),
        });
    };

    return (
        <AppLayout>
            <Head title="Vaccination Schedules" />
            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Vaccination Schedules</h1>
                        <p className="text-sm text-muted-foreground">Manage vaccine doses and recommended intervals.</p>
                    </div>
                    <Button asChild><Link href={route('vaccination-schedules.create')}><Plus className="mr-2 h-4 w-4" />Add schedule</Link></Button>
                </div>

                <form onSubmit={applyFilters} className="flex flex-col gap-3 rounded-lg border bg-card p-4 md:flex-row">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input className="pl-9" placeholder="Search schedule name..." value={search} onChange={(e) => setSearch(e.target.value)} />
                    </div>
                    <Select value={filters.vaccine_id || 'all'} onValueChange={(value) => changeFilter('vaccine_id', value === 'all' ? '' : value)}>
                        <SelectTrigger className="md:w-[220px]"><SelectValue placeholder="All vaccines" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All vaccines</SelectItem>
                            {vaccines.map((v) => <SelectItem key={v.id} value={String(v.id)}>{v.name}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Select value={filters.is_active || 'all'} onValueChange={(value) => changeFilter('is_active', value === 'all' ? '' : value)}>
                        <SelectTrigger className="md:w-[160px]"><SelectValue placeholder="All statuses" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="submit" variant="secondary">Search</Button>
                </form>

                <div className="overflow-hidden rounded-lg border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Vaccine</TableHead>
                                <TableHead>Schedule</TableHead>
                                <TableHead>Dose</TableHead>
                                <TableHead>Recommended age</TableHead>
                                <TableHead>Interval</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {schedules.data.length ? schedules.data.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell className="font-medium">{item.vaccine?.name ?? `Vaccine #${item.vaccine_id}`}</TableCell>
                                    <TableCell>{item.schedule_name || `Dose ${item.dose_number}`}</TableCell>
                                    <TableCell>{item.dose_number}</TableCell>
                                    <TableCell>{item.recommended_age_days ?? '—'} {item.recommended_age_days !== null ? 'days' : ''}</TableCell>
                                    <TableCell>{item.interval_days ?? '—'} {item.interval_days !== null ? 'days' : ''}</TableCell>
                                    <TableCell><span className={item.is_active ? 'text-green-600' : 'text-muted-foreground'}>{item.is_active ? 'Active' : 'Inactive'}</span></TableCell>
                                    <TableCell>
                                        <div className="flex justify-end gap-1">
                                            <Button variant="ghost" size="icon" asChild><Link href={route('vaccination-schedules.edit', item.id)}><Pencil className="h-4 w-4" /></Link></Button>
                                            <Button variant="ghost" size="icon" onClick={() => setDeleteId(item.id)}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )) : (
                                <TableRow><TableCell colSpan={7} className="h-24 text-center text-muted-foreground">No vaccination schedules found.</TableCell></TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <div className="flex flex-col gap-3 border-t p-4 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                        <span>Showing {schedules.from ?? 0}–{schedules.to ?? 0} of {schedules.total}</span>
                        <div className="flex flex-wrap gap-1">
                            {schedules.links.map((link, index) => (
                                <Button key={index} size="sm" variant={link.active ? 'default' : 'outline'} disabled={!link.url} asChild={!!link.url}>
                                    {link.url ? <Link href={link.url} preserveScroll>{link.label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next')}</Link> : <span>{link.label}</span>}
                                </Button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <AlertDialog open={deleteId !== null} onOpenChange={(open) => !open && setDeleteId(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete vaccination schedule?</AlertDialogTitle>
                        <AlertDialogDescription>This action cannot be undone. Existing vaccination records may reference this schedule, so deletion may be restricted by the database.</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={destroy} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">Delete</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
