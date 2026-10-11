import React, { useEffect, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import type { DepartmentItems, HospitalOption } from './types';

type Props = {
    items: DepartmentItems;
    filters: { search?: string; hospital_id?: string; status?: string; per_page?: string };
    hospitals: HospitalOption[];
    can: { create: boolean; edit: boolean; delete: boolean; assignDoctors: boolean };
};

export default function Index({ items, filters, hospitals, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [hospitalId, setHospitalId] = useState(filters.hospital_id ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [perPage, setPerPage] = useState(filters.per_page ?? '10');

    useEffect(() => {
        const timer = window.setTimeout(() => {
            router.get(route('department.index'), {
                search: search || undefined,
                hospital_id: hospitalId || undefined,
                status: status || undefined,
                per_page: perPage,
            }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
        return () => window.clearTimeout(timer);
    }, [search, hospitalId, status, perPage]);

    const destroy = (id: number) => {
        if (window.confirm('Delete this department? This will soft-delete the department record.')) {
            router.delete(route('department.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title="Department Management" />
            <div className="space-y-6 p-6">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div><h1 className="text-2xl font-semibold">Department Management</h1><p className="text-sm text-muted-foreground">Manage departments and doctor assignments.</p></div>
                    {can.create && <Button asChild><Link href={route('department.create')}>Add Department</Link></Button>}
                </div>

                <div className="grid gap-3 md:grid-cols-4">
                    <Input placeholder="Search departments..." value={search} onChange={e => setSearch(e.target.value)} />
                    <select className="h-10 rounded-md border bg-background px-3 text-sm" value={hospitalId} onChange={e => setHospitalId(e.target.value)}>
                        <option value="">All hospitals</option>
                        {hospitals.map(h => <option key={h.id} value={h.id}>{h.hospital_name}</option>)}
                    </select>
                    <select className="h-10 rounded-md border bg-background px-3 text-sm" value={status} onChange={e => setStatus(e.target.value)}>
                        <option value="">All statuses</option><option value="1">Active</option><option value="0">Inactive</option>
                    </select>
                    <select className="h-10 rounded-md border bg-background px-3 text-sm" value={perPage} onChange={e => setPerPage(e.target.value)}>
                        <option value="10">10 per page</option><option value="25">25 per page</option><option value="50">50 per page</option>
                    </select>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader><TableRow>
                            <TableHead>Department</TableHead><TableHead>Hospital</TableHead><TableHead>Floor</TableHead>
                            <TableHead>Doctors</TableHead><TableHead>Status</TableHead><TableHead className="text-right">Actions</TableHead>
                        </TableRow></TableHeader>
                        <TableBody>
                            {items.data.length === 0 ? <TableRow><TableCell colSpan={6} className="py-10 text-center text-muted-foreground">No departments found.</TableCell></TableRow> :
                                items.data.map(department => <TableRow key={department.id}>
                                    <TableCell><div className="font-medium">{department.name}</div><div className="text-xs text-muted-foreground">{department.code || 'No code'}</div></TableCell>
                                    <TableCell>{department.hospital?.hospital_name ?? '—'}</TableCell>
                                    <TableCell>{department.floor || '—'}</TableCell>
                                    <TableCell>{department.doctors_count ?? 0}</TableCell>
                                    <TableCell><Badge variant={department.status ? 'default' : 'secondary'}>{department.status ? 'Active' : 'Inactive'}</Badge></TableCell>
                                    <TableCell><div className="flex justify-end gap-2">
                                        <Button size="sm" variant="outline" asChild><Link href={route('department.show', department.id)}>View / Staff</Link></Button>
                                        {can.edit && <Button size="sm" variant="outline" asChild><Link href={route('department.edit', department.id)}>Edit</Link></Button>}
                                        {can.delete && <Button size="sm" variant="destructive" onClick={() => destroy(department.id)}>Delete</Button>}
                                    </div></TableCell>
                                </TableRow>)
                            }
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-sm text-muted-foreground">
                        Showing {items.from ?? 0}–{items.to ?? 0} of {items.total} departments
                    </p>
                    <div className="flex flex-wrap gap-1">
                        {items.links.map((link, index) => (
                            <Button key={`${link.label}-${index}`} size="sm" variant={link.active ? 'default' : 'outline'}
                                disabled={!link.url} onClick={() => link.url && router.visit(link.url)}
                                dangerouslySetInnerHTML={{ __html: link.label }} />
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
