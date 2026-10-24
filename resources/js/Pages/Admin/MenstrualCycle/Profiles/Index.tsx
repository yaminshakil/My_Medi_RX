import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Pencil, Search, Trash2 } from 'lucide-react';

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

type Profile = {
    id: number;
    user_id: number;
    name: string;
    email: string | null;
    last_period_start: string | null;
    cycle_length: number;
    period_length: number;
    is_active: boolean;
    created_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
};

type Props = {
    profiles: Paginated<Profile>;
    filters: { search?: string };
};

export default function Index({ profiles, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    const searchProfiles = () => {
        router.get(
            route('admin.menstrual-cycle.profiles.index'),
            { search },
            { preserveState: true, replace: true }
        );
    };

    const deleteProfile = (profile: Profile) => {
        if (!window.confirm(
            `Delete the cycle profile for ${profile.name}? Its daily logs will also be deleted.`
        )) {
            return;
        }

        router.delete(
            route('admin.menstrual-cycle.profiles.destroy', profile.id)
        );
    };

    return (
        <AppLayout>
            <Head title="Menstrual Cycle Profiles" />

            <div className="space-y-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Menstrual Cycle Profiles
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Manage cycle settings and tracking status.
                    </p>
                </div>

                <div className="flex max-w-xl gap-2">
                    <Input
                        placeholder="Search name or email..."
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') searchProfiles();
                        }}
                    />
                    <Button onClick={searchProfiles}>
                        <Search className="mr-2 h-4 w-4" />
                        Search
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Last period</TableHead>
                                <TableHead>Cycle</TableHead>
                                <TableHead>Period</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {profiles.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="py-8 text-center text-muted-foreground"
                                    >
                                        No cycle profiles found.
                                    </TableCell>
                                </TableRow>
                            ) : profiles.data.map((profile) => (
                                <TableRow key={profile.id}>
                                    <TableCell>
                                        <div className="font-medium">
                                            {profile.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {profile.email}
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        {profile.last_period_start ?? 'Not recorded'}
                                    </TableCell>

                                    <TableCell>
                                        {profile.cycle_length} days
                                    </TableCell>

                                    <TableCell>
                                        {profile.period_length} days
                                    </TableCell>

                                    <TableCell>
                                        <span className={
                                            profile.is_active
                                                ? 'text-green-700'
                                                : 'text-muted-foreground'
                                        }>
                                            {profile.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </TableCell>

                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-2">
                                            <Button asChild size="sm" variant="outline">
                                                <Link
                                                    href={route(
                                                        'admin.menstrual-cycle.profiles.edit',
                                                        profile.id
                                                    )}
                                                >
                                                    <Pencil className="mr-1 h-4 w-4" />
                                                    Edit
                                                </Link>
                                            </Button>

                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                onClick={() => deleteProfile(profile)}
                                            >
                                                <Trash2 className="mr-1 h-4 w-4" />
                                                Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-wrap gap-2">
                    {profiles.links.map((link, index) => (
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
