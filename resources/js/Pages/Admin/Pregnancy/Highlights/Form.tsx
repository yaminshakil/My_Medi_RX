import { useForm } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";

type Highlight = {
    id?: number;
    week: number;
    fruit_name: string;
    approx_length_cm: number | null;
    approx_weight_grams: number | null;
    description: string;
    source_reference: string | null;
    source_version: string | null;
    is_published: boolean;
};

type Props = {
    highlight?: Highlight;
};

export default function HighlightForm({ highlight }: Props) {
    const form = useForm({
        week: highlight?.week ?? 1,
        fruit_name: highlight?.fruit_name ?? "",
        approx_length_cm: highlight?.approx_length_cm ?? "",
        approx_weight_grams: highlight?.approx_weight_grams ?? "",
        description: highlight?.description ?? "",
        source_reference: highlight?.source_reference ?? "",
        source_version: highlight?.source_version ?? "",
        is_published: highlight?.is_published ?? false,
    });

    function submit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();

        if (highlight?.id) {
            form.put(route("admin.pregnancy.highlights.update", highlight.id));
        } else {
            form.post(route("admin.pregnancy.highlights.store"));
        }
    }

    return (
        <form onSubmit={submit} className="max-w-2xl space-y-5">
            <div className="space-y-2">
                <Label htmlFor="week">Pregnancy week</Label>
                <Input
                    id="week"
                    type="number"
                    min={1}
                    max={42}
                    value={form.data.week}
                    onChange={(e) => form.setData("week", Number(e.target.value))}
                />
                {form.errors.week && (
                    <p className="text-sm text-destructive">{form.errors.week}</p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="fruit_name">Fruit comparison</Label>
                <Input
                    id="fruit_name"
                    value={form.data.fruit_name}
                    onChange={(e) => form.setData("fruit_name", e.target.value)}
                />
                {form.errors.fruit_name && (
                    <p className="text-sm text-destructive">{form.errors.fruit_name}</p>
                )}
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="approx_length_cm">Approximate length (cm)</Label>
                    <Input
                        id="approx_length_cm"
                        type="number"
                        step="0.1"
                        value={form.data.approx_length_cm}
                        onChange={(e) =>
                            form.setData("approx_length_cm", e.target.value)
                        }
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="approx_weight_grams">Approximate weight (g)</Label>
                    <Input
                        id="approx_weight_grams"
                        type="number"
                        value={form.data.approx_weight_grams}
                        onChange={(e) =>
                            form.setData("approx_weight_grams", e.target.value)
                        }
                    />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="description">Weekly description</Label>
                <Textarea
                    id="description"
                    value={form.data.description}
                    onChange={(e) => form.setData("description", e.target.value)}
                />
                {form.errors.description && (
                    <p className="text-sm text-destructive">{form.errors.description}</p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="source_reference">Clinical/content source reference</Label>
                <Textarea
                    id="source_reference"
                    value={form.data.source_reference}
                    onChange={(e) =>
                        form.setData("source_reference", e.target.value)
                    }
                />
            </div>

            <div className="space-y-2">
                <Label htmlFor="source_version">Source version</Label>
                <Input
                    id="source_version"
                    value={form.data.source_version}
                    onChange={(e) => form.setData("source_version", e.target.value)}
                />
            </div>

            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.is_published}
                    onChange={(e) => form.setData("is_published", e.target.checked)}
                />
                Published and visible to users
            </label>

            <Button type="submit" disabled={form.processing}>
                {form.processing ? "Saving..." : "Save highlight"}
            </Button>
        </form>
    );
}
