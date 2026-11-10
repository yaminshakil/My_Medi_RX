import { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import StatusBadge from '@/components/vaccination/StatusBadge';
import type { Paginated, VaccinationRecord } from '@/components/vaccination/types';

type Props = { records: Paginated<VaccinationRecord> };

const dateLabel = (value: string | null) => value
    ? new Date(value).toLocaleDateString()
    : '—';

export default function Index({ records }: Props) {
    const [search, setSearch] = useState('');
    const [deleteId, setDeleteId] = useState<number | null>(null);

    const filtered = useMemo(() => records.data.filter((record) =>
        (record.vaccine?.name ?? '').toLowerCase().includes(search.toLowerCase())
        || (record.schedule?.schedule_name ?? '').toLowerCase().includes(search.toLowerCase())
        || record.status.toLowerCase().includes(search.toLowerCase())
    ), [records.data, search]);

    const destroy = () => {
        if (!deleteId) return;
        router.delete(route('admin.vaccinations.destroy', deleteId), {
            preserveScroll: true,
            onSuccess: () => setDeleteId(null),
        });
    };

    return (
        <AppLayout>
            <Head title="My Vaccinations" />
            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div><h1 className="text-2xl font-semibold tracking-tight">My Vaccinations</h1><p className="text-sm text-muted-foreground">View and manage your vaccination history.</p></div>
                    <Button asChild><Link href={route('admin.vaccinations.create')}><Plus className="mr-2 h-4 w-4" />Add vaccination</Link></Button>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                    <div className="rounded-lg border bg-card p-4"><p className="text-sm text-muted-foreground">Records on this page</p><p className="mt-1 text-2xl font-semibold">{records.data.length}</p></div>
                    <div className="rounded-lg border bg-card p-4"><p className="text-sm text-muted-foreground">Completed on this page</p><p className="mt-1 text-2xl font-semibold text-green-600">{records.data.filter((r) => r.status === 'completed').length}</p></div>
                    <div className="rounded-lg border bg-card p-4"><p className="text-sm text-muted-foreground">Scheduled on this page</p><p className="mt-1 text-2xl font-semibold text-blue-600">{records.data.filter((r) => r.status === 'scheduled').length}</p></div>
                </div>

                <div className="relative max-w-sm">
                    <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input className="pl-9" placeholder="Search this page..." value={search} onChange={(e) => setSearch(e.target.value)} />
                </div>

                <div className="overflow-hidden rounded-lg border bg-card">
                    <Table>
                        <TableHeader><TableRow>
                            <TableHead>Vaccine</TableHead><TableHead>Dose</TableHead><TableHead>Scheduled date</TableHead><TableHead>Administered at</TableHead><TableHead>Facility</TableHead><TableHead>Status</TableHead><TableHead className="text-right">Actions</TableHead>
                        </TableRow></TableHeader>
                        <TableBody>
                            {filtered.length ? filtered.map((record) => (
                                <TableRow key={record.id}>
                                    <TableCell className="font-medium">{record.vaccine?.name ?? `Vaccine #${record.vaccine_id}`}<div className="text-xs text-muted-foreground">{record.schedule?.schedule_name ?? ''}</div></TableCell>
                                    <TableCell>{record.dose_number}</TableCell>
                                    <TableCell>{dateLabel(record.scheduled_date)}</TableCell>
                                    <TableCell>{record.administered_at ? new Date(record.administered_at).toLocaleString() : '—'}</TableCell>
                                    <TableCell>{record.facility_name || '—'}</TableCell>
                                    <TableCell><StatusBadge status={record.status} /></TableCell>
                                    <TableCell><div className="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" asChild><Link href={route('admin.vaccinations.edit', record.id)}><Pencil className="h-4 w-4" /></Link></Button>
                                        <Button variant="ghost" size="icon" onClick={() => setDeleteId(record.id)}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                    </div></TableCell>
                                </TableRow>
                            )) : <TableRow><TableCell colSpan={7} className="h-24 text-center text-muted-foreground">No vaccination records found.</TableCell></TableRow>}
                        </TableBody>
                    </Table>
                    <div className="flex flex-col gap-2 border-t p-4 text-sm text-muted-foreground sm:flex-row sm:justify-between">
                        <span>Showing {records.from ?? 0}–{records.to ?? 0} of {records.total} records</span>
                        <div className="flex flex-wrap gap-1">
                            {records.links.map((link, index) => <Button key={index} size="sm" variant={link.active ? 'default' : 'outline'} disabled={!link.url} asChild={!!link.url}>{link.url ? <Link href={link.url} preserveScroll>{link.label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next')}</Link> : <span>{link.label}</span>}</Button>)}
                        </div>
                    </div>
                </div>
            </div>

            <AlertDialog open={deleteId !== null} onOpenChange={(open) => !open && setDeleteId(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader><AlertDialogTitle>Delete vaccination record?</AlertDialogTitle><AlertDialogDescription>This will remove the selected record from your vaccination history.</AlertDialogDescription></AlertDialogHeader>
                    <AlertDialogFooter><AlertDialogCancel>Cancel</AlertDialogCancel><AlertDialogAction onClick={destroy} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">Delete</AlertDialogAction></AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
