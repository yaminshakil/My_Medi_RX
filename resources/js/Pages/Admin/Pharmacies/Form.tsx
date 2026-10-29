import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import AppLayout from "@/Layouts/app-layout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEvent } from "react";

type Owner = {
    id: number;
    name: string;
    email: string;
};

type Address = {
    address_line_1: string;
    address_line_2: string;
    division: string;
    district: string;
    upazila: string;
    union: string;
    postal_code: string;
    latitude: string;
    longitude: string;
};

type PharmacyFormData = {
    user_id: string;
    name: string;
    phone: string;
    email: string;
    description: string;
    logo: File | null;
    address: Address;
};

type PharmacyRecord = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    description: string | null;
    logo: string | null;
    user_id: number;
    address: Partial<Address> | null;
};

interface PharmacyFormProps {
    owners: Owner[];
    pharmacy?: PharmacyRecord;
}

const emptyAddress: Address = {
    address_line_1: "",
    address_line_2: "",
    division: "",
    district: "",
    upazila: "",
    union: "",
    postal_code: "",
    latitude: "",
    longitude: "",
};

export default function PharmacyForm({ owners, pharmacy }: PharmacyFormProps) {
    const form = useForm<PharmacyFormData>({
        user_id: String(pharmacy?.user_id ?? ""),
        name: pharmacy?.name ?? "",
        phone: pharmacy?.phone ?? "",
        email: pharmacy?.email ?? "",
        description: pharmacy?.description ?? "",
        logo: null,
        address: { ...emptyAddress, ...pharmacy?.address },
    });

    const setAddress = (field: keyof Address, value: string) => {
        form.setData("address", { ...form.data.address, [field]: value });
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (pharmacy) {
            form.transform((data) => ({ ...data, _method: "put" }));
            form.post(route("admin.pharmacies.update", pharmacy.id), {
                forceFormData: true,
            });
            return;
        }

        form.post(route("admin.pharmacies.store"), { forceFormData: true });
    };

    const fieldError = (name: string) =>
        form.errors[name as keyof typeof form.errors] ? (
            <p className="text-sm text-destructive">
                {form.errors[name as keyof typeof form.errors]}
            </p>
        ) : null;

    return (
        <AppLayout
            breadcrumbs={[
                { title: "Pharmacies", href: route("admin.pharmacies.index") },
                {
                    title: pharmacy ? "Edit pharmacy" : "Add pharmacy",
                    href: "#",
                },
            ]}
        >
            <Head title={pharmacy ? `Edit ${pharmacy.name}` : "Add pharmacy"} />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {pharmacy ? "Edit pharmacy" : "Add pharmacy"}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage the pharmacy profile and its primary
                            location.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link
                            href={
                                pharmacy
                                    ? route(
                                          "admin.pharmacies.show",
                                          pharmacy.id,
                                      )
                                    : route("admin.pharmacies.index")
                            }
                        >
                            Cancel
                        </Link>
                    </Button>
                </div>

                <form onSubmit={submit} className="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Pharmacy profile</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-5 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="owner">Owner account</Label>
                                <select
                                    id="owner"
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                    value={form.data.user_id}
                                    onChange={(event) =>
                                        form.setData(
                                            "user_id",
                                            event.target.value,
                                        )
                                    }
                                >
                                    <option value="">
                                        Select a pharmacy owner
                                    </option>
                                    {owners.map((owner) => (
                                        <option key={owner.id} value={owner.id}>
                                            {owner.name} — {owner.email}
                                        </option>
                                    ))}
                                </select>
                                {fieldError("user_id")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Pharmacy name</Label>
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(event) =>
                                        form.setData("name", event.target.value)
                                    }
                                    required
                                />
                                {fieldError("name")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    value={form.data.phone}
                                    onChange={(event) =>
                                        form.setData(
                                            "phone",
                                            event.target.value,
                                        )
                                    }
                                />
                                {fieldError("phone")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={form.data.email}
                                    onChange={(event) =>
                                        form.setData(
                                            "email",
                                            event.target.value,
                                        )
                                    }
                                />
                                {fieldError("email")}
                            </div>
                            <div className="grid gap-2 md:col-span-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={form.data.description}
                                    onChange={(event) =>
                                        form.setData(
                                            "description",
                                            event.target.value,
                                        )
                                    }
                                    rows={4}
                                />
                                {fieldError("description")}
                            </div>
                            <div className="grid gap-2 md:col-span-2">
                                <Label htmlFor="logo">
                                    Logo (JPG, PNG, or WebP; up to 5 MB)
                                </Label>
                                <Input
                                    id="logo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    onChange={(event) =>
                                        form.setData(
                                            "logo",
                                            event.currentTarget.files?.[0] ??
                                                null,
                                        )
                                    }
                                />
                                {pharmacy?.logo && (
                                    <img
                                        src={`/storage/${pharmacy.logo}`}
                                        alt={`${pharmacy.name} logo`}
                                        className="h-16 w-16 rounded-lg border object-cover"
                                    />
                                )}
                                {fieldError("logo")}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Primary address</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-5 md:grid-cols-2">
                            <div className="grid gap-2 md:col-span-2">
                                <Label htmlFor="address_line_1">
                                    Address line 1
                                </Label>
                                <Input
                                    id="address_line_1"
                                    value={form.data.address.address_line_1}
                                    onChange={(event) =>
                                        setAddress(
                                            "address_line_1",
                                            event.target.value,
                                        )
                                    }
                                />
                                {fieldError("address.address_line_1")}
                            </div>
                            <div className="grid gap-2 md:col-span-2">
                                <Label htmlFor="address_line_2">
                                    Address line 2
                                </Label>
                                <Input
                                    id="address_line_2"
                                    value={form.data.address.address_line_2}
                                    onChange={(event) =>
                                        setAddress(
                                            "address_line_2",
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                            {(
                                [
                                    "division",
                                    "district",
                                    "upazila",
                                    "union",
                                    "postal_code",
                                ] as const
                            ).map((field) => (
                                <div key={field} className="grid gap-2">
                                    <Label htmlFor={field}>
                                        {field
                                            .replace("_", " ")
                                            .replace(/\b\w/g, (letter) =>
                                                letter.toUpperCase(),
                                            )}
                                    </Label>
                                    <Input
                                        id={field}
                                        value={form.data.address[field]}
                                        onChange={(event) =>
                                            setAddress(
                                                field,
                                                event.target.value,
                                            )
                                        }
                                    />
                                    {fieldError(`address.${field}`)}
                                </div>
                            ))}
                            <div className="grid gap-2">
                                <Label htmlFor="latitude">Latitude</Label>
                                <Input
                                    id="latitude"
                                    type="number"
                                    step="any"
                                    value={form.data.address.latitude}
                                    onChange={(event) =>
                                        setAddress(
                                            "latitude",
                                            event.target.value,
                                        )
                                    }
                                />
                                {fieldError("address.latitude")}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="longitude">Longitude</Label>
                                <Input
                                    id="longitude"
                                    type="number"
                                    step="any"
                                    value={form.data.address.longitude}
                                    onChange={(event) =>
                                        setAddress(
                                            "longitude",
                                            event.target.value,
                                        )
                                    }
                                />
                                {fieldError("address.longitude")}
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-3">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing
                                ? "Saving…"
                                : pharmacy
                                  ? "Save changes"
                                  : "Create pharmacy"}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
