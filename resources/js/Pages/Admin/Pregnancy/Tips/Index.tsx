import AppLayout from "@/Layouts/app-layout";
import { Head, Link, router } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Pencil, Plus, Trash2 } from "lucide-react";
import { type BreadcrumbItem } from '@/types';

type Tip = {
    id: number;
    title: string;
    icon: string;
    trimester: number | null;
    week_start: number;
    week_end: number;
    is_published: boolean;
};

type Props = {
    tips: {
        data: Tip[];
        links: { url: string | null; label: string; active: boolean }[];
    };
};

export default function Index({ tips }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pregnancy Tips', href: '/admin/pregnancy/tips' }];
    function remove(id: number) {
        if (!window.confirm("Delete this pregnancy tip?")) return;

        router.delete(route("admin.pregnancy.tips.destroy", id), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pregnancy Tips" />

            <div className="space-y-6 p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">Pregnancy Tips</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage tips by trimester and gestational week.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={route("admin.pregnancy.tips.create")}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Tip
                        </Link>
                    </Button>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Trimester</TableHead>
                                <TableHead>Weeks</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {tips.data.length ? (
                                tips.data.map((tip) => (
                                    <TableRow key={tip.id}>
                                        <TableCell>{tip.title}</TableCell>
                                        <TableCell>
                                            {tip.trimester ?? "All"}
                                        </TableCell>
                                        <TableCell>
                                            {tip.week_start}–{tip.week_end}
                                        </TableCell>
                                        <TableCell>
                                            {tip.is_published
                                                ? "Published"
                                                : "Draft"}
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
                                                            "admin.pregnancy.tips.edit",
                                                            tip.id,
                                                        )}
                                                        aria-label="Edit tip"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>

                                                <Button
                                                    variant="destructive"
                                                    size="icon"
                                                    onClick={() => remove(tip.id)}
                                                    aria-label="Delete tip"
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
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        No pregnancy tips found.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-col gap-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        Showing {tips.from} to {tips.to} {" "} from total {" "}
                        {tips.total} Highlights
                    </span>
                    <div className="flex flex-wrap gap-2">
                        {tips.links.map((link, index) => (
                            <Button
                                key={`${index}-${link.label}`}
                                size="sm"
                                variant={link.active ? "default" : "outline"}
                                disabled={!link.url}
                                onClick={() => {
                                    if (link.url) {
                                        router.get(link.url, {}, {
                                            preserveScroll: true,
                                            preserveState: true,
                                        });
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
