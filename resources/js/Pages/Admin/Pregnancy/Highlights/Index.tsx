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

type Highlight = {
    id: number;
    week: number;
    fruit_name: string;
    approx_length_cm: string | number | null;
    approx_weight_grams: number | null;
    is_published: boolean;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    highlights: {
        data: Highlight[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
    };
};

export default function Index({ highlights }: Props) {
    function remove(id: number) {
        if (!window.confirm("Delete this weekly highlight?")) return;

        router.delete(route("admin.pregnancy.highlights.destroy", id), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout>
            <Head title="Pregnancy Weekly Highlights" />

            <div className="space-y-6 p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Weekly Highlights
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage baby-size comparisons and weekly development.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={route("admin.pregnancy.highlights.create")}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Highlight
                        </Link>
                    </Button>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Week</TableHead>
                                <TableHead>Fruit</TableHead>
                                <TableHead>Length (cm)</TableHead>
                                <TableHead>Weight (g)</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {highlights.data.length ? (
                                highlights.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.week}</TableCell>
                                        <TableCell>{item.fruit_name}</TableCell>
                                        <TableCell>
                                            {item.approx_length_cm ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            {item.approx_weight_grams ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            {item.is_published
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
                                                            "admin.pregnancy.highlights.edit",
                                                            item.id,
                                                        )}
                                                        aria-label="Edit highlight"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>

                                                <Button
                                                    variant="destructive"
                                                    size="icon"
                                                    onClick={() =>
                                                        remove(item.id)
                                                    }
                                                    aria-label="Delete highlight"
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
                                        colSpan={6}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        No weekly highlights found.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-wrap gap-2">
                    {highlights.links.map((link, index) => (
                        <Button
                            key={`${index}-${link.label}`}
                            variant={link.active ? "default" : "outline"}
                            size="sm"
                            disabled={!link.url}
                            onClick={() => {
                                if (link.url) {
                                    router.get(
                                        link.url,
                                        {},
                                        {
                                            preserveScroll: true,
                                            preserveState: true,
                                        },
                                    );
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
        </AppLayout>
    );
}
