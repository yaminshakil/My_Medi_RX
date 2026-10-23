import { useForm } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";

type Tip = {
    id?: number;
    title: string;
    description: string;
    icon: string;
    color_hex: string;
    trimester: number | null;
    week_start: number;
    week_end: number;
    sort_order: number;
    is_published: boolean;
    source_reference: string | null;
};

type Props = { tip?: Tip };

export default function TipForm({ tip }: Props) {
    const form = useForm({
        title: tip?.title ?? "",
        description: tip?.description ?? "",
        icon: tip?.icon ?? "heart",
        color_hex: tip?.color_hex ?? "#4CAF50",
        trimester: tip?.trimester?.toString() ?? "",
        week_start: tip?.week_start ?? 1,
        week_end: tip?.week_end ?? 40,
        sort_order: tip?.sort_order ?? 0,
        is_published: tip?.is_published ?? false,
        source_reference: tip?.source_reference ?? "",
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const payload = {
            ...form.data,
            trimester: form.data.trimester
                ? Number(form.data.trimester)
                : null,
        };

        if (tip?.id) {
            form.put(route("admin.pregnancy.tips.update", tip.id));
        } else {
            form.post(route("admin.pregnancy.tips.store"));
        }
    }

    return (
        <form onSubmit={submit} className="max-w-3xl space-y-5">
            <div className="space-y-2">
                <Label htmlFor="title">Tip title</Label>
                <Input
                    id="title"
                    value={form.data.title}
                    onChange={(e) => form.setData("title", e.target.value)}
                />
                {form.errors.title && (
                    <p className="text-sm text-destructive">
                        {form.errors.title}
                    </p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    rows={4}
                    value={form.data.description}
                    onChange={(e) =>
                        form.setData("description", e.target.value)
                    }
                />
                {form.errors.description && (
                    <p className="text-sm text-destructive">
                        {form.errors.description}
                    </p>
                )}
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="icon">Lucide icon name</Label>
                    <Input
                        id="icon"
                        placeholder="droplets"
                        value={form.data.icon}
                        onChange={(e) => form.setData("icon", e.target.value)}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="color_hex">Color</Label>
                    <Input
                        id="color_hex"
                        type="color"
                        value={form.data.color_hex}
                        onChange={(e) =>
                            form.setData("color_hex", e.target.value)
                        }
                    />
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <div className="space-y-2">
                    <Label htmlFor="trimester">Trimester</Label>
                    <select
                        id="trimester"
                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        value={form.data.trimester}
                        onChange={(e) =>
                            form.setData("trimester", e.target.value)
                        }
                    >
                        <option value="">All trimesters</option>
                        <option value="1">First</option>
                        <option value="2">Second</option>
                        <option value="3">Third</option>
                    </select>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="week_start">Starting week</Label>
                    <Input
                        id="week_start"
                        type="number"
                        min={1}
                        max={42}
                        value={form.data.week_start}
                        onChange={(e) =>
                            form.setData("week_start", Number(e.target.value))
                        }
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="week_end">Ending week</Label>
                    <Input
                        id="week_end"
                        type="number"
                        min={1}
                        max={42}
                        value={form.data.week_end}
                        onChange={(e) =>
                            form.setData("week_end", Number(e.target.value))
                        }
                    />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="sort_order">Display order</Label>
                <Input
                    id="sort_order"
                    type="number"
                    min={0}
                    value={form.data.sort_order}
                    onChange={(e) =>
                        form.setData("sort_order", Number(e.target.value))
                    }
                />
            </div>

            <div className="space-y-2">
                <Label htmlFor="source_reference">Source reference</Label>
                <Textarea
                    id="source_reference"
                    value={form.data.source_reference}
                    onChange={(e) =>
                        form.setData("source_reference", e.target.value)
                    }
                />
            </div>

            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.is_published}
                    onChange={(e) =>
                        form.setData("is_published", e.target.checked)
                    }
                />
                Published and visible to users
            </label>

            <Button type="submit" disabled={form.processing}>
                {form.processing
                    ? "Saving..."
                    : tip?.id
                        ? "Update Tip"
                        : "Create Tip"}
            </Button>
        </form>
    );
}
