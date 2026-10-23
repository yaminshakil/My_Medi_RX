
import { Head, Link, router } from "@inertiajs/react";
import { Pencil, Plus, Trash2 } from "lucide-react";
import AppLayout from '@/Layouts/app-layout';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { type BreadcrumbItem } from '@/types';

type Profile = {
    id: number;
    user_id: number;
    last_menstrual_period: string;
    due_date: string;
    dating_confirmed_date: string | null;
    is_active: boolean;
    name: string;
    email: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    profiles: {
        data: Profile[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
    };
};

export default function Index({ profiles, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pregnancy Profiles', href: '/admin/pregnancy/profiles' }];
    function search(value: string) {
        router.get(
            route("admin.pregnancy.profiles.index"),
            { search: value || undefined },
            { preserveState: true, replace: true }
        );
    }

    function removeProfile(id: number) {
        if (window.confirm("Delete this pregnancy profile?")) {
            router.delete(route("admin.pregnancy.profiles.destroy", id));
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pregnancy Profiles" />

            <div className="space-y-6 p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Pregnancy Profiles
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage patient pregnancy tracking records.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={route("admin.pregnancy.profiles.create")}>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Profile
                        </Link>
                    </Button>
                </div>

                <Input
                    placeholder="Search patient name or email..."
                    defaultValue={filters.search ?? ""}
                    onChange={(event) => search(event.target.value)}
                    className="max-w-sm"
                />

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Patient</TableHead>
                                <TableHead>LMP</TableHead>
                                <TableHead>Due Date</TableHead>
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
                                        colSpan={5}
                                        className="py-8 text-center"
                                    >
                                        No pregnancy profiles found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                profiles.data.map((profile) => (
                                    <TableRow key={profile.id}>
                                        <TableCell>
                                            <div className="font-medium">
                                                {profile?.name ??
                                                    `User #${profile.user_id}`}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {profile?.email ?? ""}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            {profile.last_menstrual_period}
                                        </TableCell>

                                        <TableCell>
                                            {profile.due_date}
                                        </TableCell>

                                        <TableCell>
                                            <span
                                                className={
                                                    profile.is_active
                                                        ? "text-green-600"
                                                        : "text-muted-foreground"
                                                }
                                            >
                                                {profile.is_active
                                                    ? "Active"
                                                    : "Inactive"}
                                            </span>
                                        </TableCell>

                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={route(
                                                            "admin.pregnancy.profiles.edit",
                                                            profile.id
                                                        )}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>

                                                <Button
                                                    variant="destructive"
                                                    size="icon"
                                                    onClick={() =>
                                                        removeProfile(profile.id)
                                                    }
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
                <div className="flex flex-col gap-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        Showing {profiles.from} to {profiles.to} {" "} from total {" "}
                        {profiles.total} Highlights
                    </span>
                    <div className="flex flex-wrap gap-2">
                        {profiles.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? "default" : "outline"}
                                size="sm"
                                disabled={!link.url}
                                onClick={() => {
                                    if (link.url) {
                                        router.visit(link.url);
                                    }
                                }}
                            >
                                <span
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            </Button>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
