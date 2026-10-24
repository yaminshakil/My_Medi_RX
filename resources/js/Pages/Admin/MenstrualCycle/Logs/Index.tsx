import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Search } from 'lucide-react';

import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

type CycleLog = {
    id: number;
    uuid: string;
    user_name: string;
    email: string | null;
    entry_date: string;
    flow: string;
    cramps: string;
    mood: string | null;
    water_glasses: number;
    symptoms: string[];
    notes: string | null;
};

type Props = {
    logs: {
        data: CycleLog[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; date?: string };
};

export default function Index({ logs, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [date, setDate] = useState(filters.date ?? '');

    const applyFilters = () => {
        router.get(
            route('admin.menstrual-cycle.logs.index'),
            { search, date },
            { preserveState: true, replace: true }
        );
    };

    return (
        <AppLayout>
            <Head title="Menstrual Cycle Logs" />

            <div className="space-y-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Daily Cycle Logs
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Review daily flow, cramps, mood, and water intake.
                    </p>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Input
                        placeholder="Search user name or email..."
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') applyFilters();
                        }}
                    />

                    <Input
                        aria-label="Filter by date"
                        type="date"
                        value={date}
                        onChange={(event) => setDate(event.target.value)}
                    />

                    <Button onClick={applyFilters}>
                        <Search className="mr-2 h-4 w-4" />
                        Filter
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>User</TableHead>
                                <TableHead>Flow</TableHead>
                                <TableHead>Cramps</TableHead>
                                <TableHead>Mood</TableHead>
                                <TableHead>Water</TableHead>
                                <TableHead>Symptoms</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {logs.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="py-8 text-center text-muted-foreground"
                                    >
                                        No cycle logs found.
                                    </TableCell>
                                </TableRow>
                            ) : logs.data.map((log) => (
                                <TableRow key={log.uuid}>
                                    <TableCell>{log.entry_date}</TableCell>

                                    <TableCell>
                                        <div className="font-medium">
                                            {log.user_name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {log.email}
                                        </div>
                                    </TableCell>

                                    <TableCell>{log.flow}</TableCell>
                                    <TableCell>{log.cramps}</TableCell>
                                    <TableCell>{log.mood ?? '—'}</TableCell>
                                    <TableCell>{log.water_glasses} glasses</TableCell>

                                    <TableCell>
                                        {log.symptoms.length
                                            ? log.symptoms.join(', ')
                                            : '—'}
                                        {log.notes && (
                                            <p className="mt-1 max-w-xs whitespace-normal text-xs text-muted-foreground">
                                                {log.notes}
                                            </p>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-wrap gap-2">
                    {logs.links.map((link, index) => (
                        <Button
                            key={`${index}-${link.label}`}
                            size="sm"
                            variant={link.active ? 'default' : 'outline'}
                            disabled={!link.url}
                            onClick={() => link.url && router.get(
                                link.url,
                                {},
                                { preserveState: true }
                            )}
                        >
                            {link.label
                                .replace('&laquo;', '«')
                                .replace('&raquo;', '»')}
                        </Button>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
