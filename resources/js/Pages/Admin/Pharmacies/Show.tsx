import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import AppLayout from "@/Layouts/app-layout";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { FormEvent, useState } from "react";

type License = {
    id: number;
    license_number: string;
    license_type: string;
    issued_date: string | null;
    expiry_date: string | null;
    document: string | null;
    status: string;
    rejection_reason: string | null;
    verifier: { name: string } | null;
};

type Pharmacist = {
    id: number;
    name: string;
    registration_number: string | null;
    phone: string | null;
    email: string | null;
    qualification: string | null;
    registration_document: string | null;
    is_primary: boolean;
    status: string;
    rejection_reason: string | null;
    verifier: { name: string } | null;
};

type Pharmacy = {
    id: number;
    name: string;
    slug: string;
    phone: string | null;
    email: string | null;
    description: string | null;
    logo: string | null;
    status: string;
    rejection_reason: string | null;
    verified_at: string | null;
    owner: { name: string; email: string } | null;
    verifier: { name: string } | null;
    address: Record<string, string | boolean | null> | null;
    licenses: License[];
    pharmacists: Pharmacist[];
};

type ReviewTarget = {
    type: "pharmacy" | "license" | "pharmacist";
    id?: number;
};

type LicenseFormData = {
    license_number: string;
    license_type: string;
    issued_date: string;
    expiry_date: string;
    document: File | null;
};

type PharmacistFormData = {
    name: string;
    registration_number: string;
    phone: string;
    email: string;
    qualification: string;
    registration_document: File | null;
    is_primary: boolean;
};

const badgeClass: Record<string, string> = {
    draft: "border-slate-200 bg-slate-100 text-slate-700",
    pending: "border-amber-200 bg-amber-50 text-amber-800",
    approved: "border-emerald-200 bg-emerald-50 text-emerald-800",
    verified: "border-emerald-200 bg-emerald-50 text-emerald-800",
    rejected: "border-rose-200 bg-rose-50 text-rose-800",
    suspended: "border-orange-200 bg-orange-50 text-orange-800",
    expired: "border-slate-200 bg-slate-100 text-slate-700",
};

const statusBadge = (status: string) => (
    <Badge variant="outline" className={badgeClass[status] ?? ""}>
        {status}
    </Badge>
);

export default function Show({ pharmacy }: { pharmacy: Pharmacy }) {
    const [licenseDialogOpen, setLicenseDialogOpen] = useState(false);
    const [pharmacistDialogOpen, setPharmacistDialogOpen] = useState(false);
    const [selectedLicense, setSelectedLicense] = useState<License | null>(
        null,
    );
    const [selectedPharmacist, setSelectedPharmacist] =
        useState<Pharmacist | null>(null);
    const [reviewTarget, setReviewTarget] = useState<ReviewTarget | null>(null);
    const { errors: pageErrors, flash } = usePage().props as {
        errors?: Record<string, string>;
        flash?: { message?: { success?: string; error?: string } };
    };

    const licenseForm = useForm<LicenseFormData>({
        license_number: "",
        license_type: "drug_license",
        issued_date: "",
        expiry_date: "",
        document: null,
    });
    const pharmacistForm = useForm<PharmacistFormData>({
        name: "",
        registration_number: "",
        phone: "",
        email: "",
        qualification: "",
        registration_document: null,
        is_primary: false,
    });
    const rejectionForm = useForm({ rejection_reason: "" });
    const statusForm = useForm({ status: "approved", rejection_reason: "" });

    const submitLicense = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const options = {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setLicenseDialogOpen(false);
                setSelectedLicense(null);
                licenseForm.reset();
            },
            onFinish: () => licenseForm.transform((data) => data),
        };

        if (selectedLicense) {
            licenseForm.transform((data) => ({ ...data, _method: "put" }));
            licenseForm.post(
                route("admin.pharmacies.licenses.update", [
                    pharmacy.id,
                    selectedLicense.id,
                ]),
                options,
            );
            return;
        }

        licenseForm.post(
            route("admin.pharmacies.licenses.store", pharmacy.id),
            options,
        );
    };

    const submitPharmacist = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const options = {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setPharmacistDialogOpen(false);
                setSelectedPharmacist(null);
                pharmacistForm.reset();
            },
            onFinish: () => pharmacistForm.transform((data) => data),
        };

        if (selectedPharmacist) {
            pharmacistForm.transform((data) => ({ ...data, _method: "put" }));
            pharmacistForm.post(
                route("admin.pharmacies.pharmacists.update", [
                    pharmacy.id,
                    selectedPharmacist.id,
                ]),
                options,
            );
            return;
        }

        pharmacistForm.post(
            route("admin.pharmacies.pharmacists.store", pharmacy.id),
            options,
        );
    };

    const editLicense = (license: License) => {
        setSelectedLicense(license);
        licenseForm.setData({
            license_number: license.license_number,
            license_type: license.license_type,
            issued_date: license.issued_date ?? "",
            expiry_date: license.expiry_date ?? "",
            document: null,
        });
        setLicenseDialogOpen(true);
    };

    const editPharmacist = (pharmacist: Pharmacist) => {
        setSelectedPharmacist(pharmacist);
        pharmacistForm.setData({
            name: pharmacist.name,
            registration_number: pharmacist.registration_number ?? "",
            phone: pharmacist.phone ?? "",
            email: pharmacist.email ?? "",
            qualification: pharmacist.qualification ?? "",
            registration_document: null,
            is_primary: pharmacist.is_primary,
        });
        setPharmacistDialogOpen(true);
    };

    const openNewLicense = () => {
        setSelectedLicense(null);
        licenseForm.reset();
        licenseForm.setData("license_type", "drug_license");
        setLicenseDialogOpen(true);
    };

    const openNewPharmacist = () => {
        setSelectedPharmacist(null);
        pharmacistForm.reset();
        setPharmacistDialogOpen(true);
    };

    const reviewUrl = (target: ReviewTarget) => {
        if (target.type === "pharmacy") {
            return route("admin.pharmacies.status", pharmacy.id);
        }

        if (target.type === "license") {
            return route("admin.pharmacies.licenses.review", [
                pharmacy.id,
                target.id,
            ]);
        }

        return route("admin.pharmacies.pharmacists.review", [
            pharmacy.id,
            target.id,
        ]);
    };

    const submitRejection = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (!reviewTarget) {
            return;
        }
        if (reviewTarget.type === "pharmacy") {
            statusForm.transform(() => ({
                status: "rejected",
                rejection_reason: rejectionForm.data.rejection_reason,
            }));
            statusForm.patch(reviewUrl(reviewTarget), {
                preserveScroll: true,
                onSuccess: () => {
                    setReviewTarget(null);
                    rejectionForm.reset();
                },
            });
            return;
        }

        router.patch(
            reviewUrl(reviewTarget),
            {
                status: "rejected",
                rejection_reason: rejectionForm.data.rejection_reason,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setReviewTarget(null);
                    rejectionForm.reset();
                },
            },
        );
    };

    const verify = (target: ReviewTarget) => {
        if (target.type === "pharmacy") {
            statusForm.transform(() => ({
                status: "approved",
                rejection_reason: "",
            }));
            statusForm.patch(reviewUrl(target), { preserveScroll: true });
            return;
        }

        router.patch(
            reviewUrl(target),
            { status: "verified" },
            { preserveScroll: true },
        );
    };

    const setPharmacyStatus = (status: "approved" | "suspended") => {
        statusForm.transform(() => ({ status, rejection_reason: "" }));
        statusForm.patch(reviewUrl({ type: "pharmacy" }), {
            preserveScroll: true,
        });
    };

    const removeLicense = (license: License) => {
        if (window.confirm(`Delete license ${license.license_number}?`)) {
            router.delete(
                route("admin.pharmacies.licenses.destroy", [
                    pharmacy.id,
                    license.id,
                ]),
                { preserveScroll: true },
            );
        }
    };

    const removePharmacist = (pharmacist: Pharmacist) => {
        if (window.confirm(`Delete pharmacist ${pharmacist.name}?`)) {
            router.delete(
                route("admin.pharmacies.pharmacists.destroy", [
                    pharmacy.id,
                    pharmacist.id,
                ]),
                { preserveScroll: true },
            );
        }
    };

    const removePharmacy = () => {
        if (
            window.confirm(
                `Delete ${pharmacy.name} and all of its licenses and pharmacist records?`,
            )
        ) {
            router.delete(route("admin.pharmacies.destroy", pharmacy.id));
        }
    };

    const errors = licenseForm.errors as Record<string, string>;
    const pharmacistErrors = pharmacistForm.errors as Record<string, string>;

    return (
        <AppLayout
            breadcrumbs={[
                { title: "Pharmacies", href: route("admin.pharmacies.index") },
                {
                    title: pharmacy.name,
                    href: route("admin.pharmacies.show", pharmacy.id),
                },
            ]}
        >
            <Head title={pharmacy.name} />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex items-center gap-4">
                        {pharmacy.logo ? (
                            <img
                                src={`/storage/${pharmacy.logo}`}
                                alt=""
                                className="size-16 rounded-xl border object-cover"
                            />
                        ) : (
                            <div className="size-16 rounded-xl bg-primary/10" />
                        )}
                        <div>
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="text-2xl font-semibold tracking-tight">
                                    {pharmacy.name}
                                </h1>
                                {statusBadge(pharmacy.status)}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Registration #{pharmacy.id} · Owner:{" "}
                                {pharmacy.owner?.name ?? "Unknown"}
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link
                                href={route(
                                    "admin.pharmacies.edit",
                                    pharmacy.id,
                                )}
                            >
                                Edit profile
                            </Link>
                        </Button>
                        <Button variant="destructive" onClick={removePharmacy}>
                            Delete pharmacy
                        </Button>
                        {!["approved", "suspended"].includes(
                            pharmacy.status,
                        ) && (
                            <Button
                                onClick={() => verify({ type: "pharmacy" })}
                            >
                                Approve pharmacy
                            </Button>
                        )}
                        {pharmacy.status !== "rejected" && (
                            <Button
                                variant="outline"
                                onClick={() => {
                                    rejectionForm.reset();
                                    setReviewTarget({ type: "pharmacy" });
                                }}
                            >
                                Reject
                            </Button>
                        )}
                        {pharmacy.status !== "suspended" && (
                            <Button
                                variant="destructive"
                                onClick={() => setPharmacyStatus("suspended")}
                            >
                                Suspend
                            </Button>
                        )}
                        {pharmacy.status === "suspended" && (
                            <Button
                                onClick={() => setPharmacyStatus("approved")}
                            >
                                Reinstate
                            </Button>
                        )}
                    </div>
                </div>
                {flash?.message?.success && (
                    <p
                        role="status"
                        className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        {flash.message.success}
                    </p>
                )}
                {flash?.message?.error && (
                    <p
                        role="alert"
                        className="rounded-md border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive"
                    >
                        {flash.message.error}
                    </p>
                )}
                {statusForm.errors.status && (
                    <p role="alert" className="text-sm text-destructive">
                        {statusForm.errors.status}
                    </p>
                )}
                {pageErrors?.license && (
                    <p role="alert" className="text-sm text-destructive">
                        {pageErrors.license}
                    </p>
                )}

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Pharmacy profile</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs uppercase text-muted-foreground">
                                        Owner
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {pharmacy.owner?.name ??
                                            "Unknown owner"}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {pharmacy.owner?.email}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs uppercase text-muted-foreground">
                                        Contact
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {pharmacy.phone || "No phone"}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {pharmacy.email || "No email"}
                                    </p>
                                </div>
                                <div className="sm:col-span-2">
                                    <p className="text-xs uppercase text-muted-foreground">
                                        Address
                                    </p>
                                    <p className="mt-1">
                                        {pharmacy.address
                                            ? [
                                                  pharmacy.address
                                                      .address_line_1,
                                                  pharmacy.address
                                                      .address_line_2,
                                                  pharmacy.address.upazila,
                                                  pharmacy.address.district,
                                                  pharmacy.address.division,
                                                  pharmacy.address.postal_code,
                                              ]
                                                  .filter(Boolean)
                                                  .join(", ")
                                            : "No address provided"}
                                    </p>
                                </div>
                                <div className="sm:col-span-2">
                                    <p className="text-xs uppercase text-muted-foreground">
                                        Description
                                    </p>
                                    <p className="mt-1 whitespace-pre-line">
                                        {pharmacy.description ||
                                            "No description provided."}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs uppercase text-muted-foreground">
                                        Reviewed by
                                    </p>
                                    <p className="mt-1">
                                        {pharmacy.verifier?.name ??
                                            "Not reviewed"}
                                    </p>
                                </div>
                                {pharmacy.rejection_reason && (
                                    <div>
                                        <p className="text-xs uppercase text-muted-foreground">
                                            Rejection reason
                                        </p>
                                        <p className="mt-1 text-destructive">
                                            {pharmacy.rejection_reason}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex-row items-center justify-between space-y-0">
                                <div>
                                    <CardTitle>Pharmacy licenses</CardTitle>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Review and manage license documents.
                                    </p>
                                </div>
                                <Button size="sm" onClick={openNewLicense}>
                                    Add license
                                </Button>
                            </CardHeader>
                            <CardContent className="overflow-x-auto">
                                <table className="w-full min-w-[700px] text-left text-sm">
                                    <thead className="border-b text-xs uppercase text-muted-foreground">
                                        <tr>
                                            <th className="py-3">License</th>
                                            <th className="py-3">Expiry</th>
                                            <th className="py-3">Status</th>
                                            <th className="py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {pharmacy.licenses.map((license) => (
                                            <tr key={license.id}>
                                                <td className="py-3">
                                                    <p className="font-medium">
                                                        {license.license_number}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {license.license_type}
                                                    </p>
                                                    {license.verifier?.name && (
                                                        <p className="text-xs text-muted-foreground">
                                                            Reviewed by{" "}
                                                            {
                                                                license.verifier
                                                                    .name
                                                            }
                                                        </p>
                                                    )}
                                                    {license.rejection_reason && (
                                                        <p className="mt-1 text-xs text-destructive">
                                                            {
                                                                license.rejection_reason
                                                            }
                                                        </p>
                                                    )}
                                                </td>
                                                <td className="py-3">
                                                    {license.expiry_date ||
                                                        "No expiry date"}
                                                </td>
                                                <td className="py-3">
                                                    {statusBadge(
                                                        license.status,
                                                    )}
                                                </td>
                                                <td className="py-3">
                                                    <div className="flex justify-end gap-1">
                                                        {license.document && (
                                                            <Button
                                                                asChild
                                                                size="sm"
                                                                variant="outline"
                                                            >
                                                                <a
                                                                    href={route(
                                                                        "admin.pharmacies.licenses.document",
                                                                        [
                                                                            pharmacy.id,
                                                                            license.id,
                                                                        ],
                                                                    )}
                                                                >
                                                                    Document
                                                                </a>
                                                            </Button>
                                                        )}
                                                        {license.status ===
                                                            "pending" && (
                                                            <Button
                                                                size="sm"
                                                                onClick={() =>
                                                                    verify({
                                                                        type: "license",
                                                                        id: license.id,
                                                                    })
                                                                }
                                                            >
                                                                Verify
                                                            </Button>
                                                        )}
                                                        {license.status !==
                                                            "rejected" && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => {
                                                                    rejectionForm.reset();
                                                                    setReviewTarget(
                                                                        {
                                                                            type: "license",
                                                                            id: license.id,
                                                                        },
                                                                    );
                                                                }}
                                                            >
                                                                Reject
                                                            </Button>
                                                        )}
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() =>
                                                                editLicense(
                                                                    license,
                                                                )
                                                            }
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() =>
                                                                removeLicense(
                                                                    license,
                                                                )
                                                            }
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                        {pharmacy.licenses.length === 0 && (
                                            <tr>
                                                <td
                                                    colSpan={4}
                                                    className="py-8 text-center text-muted-foreground"
                                                >
                                                    No licenses have been added.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex-row items-center justify-between space-y-0">
                                <div>
                                    <CardTitle>Pharmacists</CardTitle>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Manage pharmacist registrations and
                                        credentials.
                                    </p>
                                </div>
                                <Button size="sm" onClick={openNewPharmacist}>
                                    Add pharmacist
                                </Button>
                            </CardHeader>
                            <CardContent className="overflow-x-auto">
                                <table className="w-full min-w-[800px] text-left text-sm">
                                    <thead className="border-b text-xs uppercase text-muted-foreground">
                                        <tr>
                                            <th className="py-3">Pharmacist</th>
                                            <th className="py-3">
                                                Registration
                                            </th>
                                            <th className="py-3">Status</th>
                                            <th className="py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {pharmacy.pharmacists.map(
                                            (pharmacist) => (
                                                <tr key={pharmacist.id}>
                                                    <td className="py-3">
                                                        <p className="font-medium">
                                                            {pharmacist.name}{" "}
                                                            {pharmacist.is_primary && (
                                                                <Badge variant="secondary">
                                                                    Primary
                                                                </Badge>
                                                            )}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {pharmacist.qualification ||
                                                                pharmacist.email ||
                                                                pharmacist.phone ||
                                                                "No additional details"}
                                                        </p>
                                                        {pharmacist.rejection_reason && (
                                                            <p className="mt-1 text-xs text-destructive">
                                                                {
                                                                    pharmacist.rejection_reason
                                                                }
                                                            </p>
                                                        )}
                                                    </td>
                                                    <td className="py-3">
                                                        {pharmacist.registration_number ||
                                                            "Not provided"}
                                                    </td>
                                                    <td className="py-3">
                                                        {statusBadge(
                                                            pharmacist.status,
                                                        )}
                                                    </td>
                                                    <td className="py-3">
                                                        <div className="flex justify-end gap-1">
                                                            {pharmacist.registration_document && (
                                                                <Button
                                                                    asChild
                                                                    size="sm"
                                                                    variant="outline"
                                                                >
                                                                    <a
                                                                        href={route(
                                                                            "admin.pharmacies.pharmacists.document",
                                                                            [
                                                                                pharmacy.id,
                                                                                pharmacist.id,
                                                                            ],
                                                                        )}
                                                                    >
                                                                        Document
                                                                    </a>
                                                                </Button>
                                                            )}
                                                            {pharmacist.status ===
                                                                "pending" && (
                                                                <Button
                                                                    size="sm"
                                                                    onClick={() =>
                                                                        verify({
                                                                            type: "pharmacist",
                                                                            id: pharmacist.id,
                                                                        })
                                                                    }
                                                                >
                                                                    Verify
                                                                </Button>
                                                            )}
                                                            {pharmacist.status !==
                                                                "rejected" && (
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                    onClick={() => {
                                                                        rejectionForm.reset();
                                                                        setReviewTarget(
                                                                            {
                                                                                type: "pharmacist",
                                                                                id: pharmacist.id,
                                                                            },
                                                                        );
                                                                    }}
                                                                >
                                                                    Reject
                                                                </Button>
                                                            )}
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() =>
                                                                    editPharmacist(
                                                                        pharmacist,
                                                                    )
                                                                }
                                                            >
                                                                Edit
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="destructive"
                                                                onClick={() =>
                                                                    removePharmacist(
                                                                        pharmacist,
                                                                    )
                                                                }
                                                            >
                                                                Delete
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ),
                                        )}
                                        {pharmacy.pharmacists.length === 0 && (
                                            <tr>
                                                <td
                                                    colSpan={4}
                                                    className="py-8 text-center text-muted-foreground"
                                                >
                                                    No pharmacists have been
                                                    added.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </div>

                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle>Review checklist</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3 text-sm">
                            <p>
                                Current licenses:{" "}
                                <strong>
                                    {
                                        pharmacy.licenses.filter(
                                            (license) =>
                                                license.status === "verified",
                                        ).length
                                    }{" "}
                                    verified
                                </strong>{" "}
                                of {pharmacy.licenses.length}
                            </p>
                            <p>
                                Pharmacists:{" "}
                                <strong>
                                    {
                                        pharmacy.pharmacists.filter(
                                            (pharmacist) =>
                                                pharmacist.status ===
                                                "verified",
                                        ).length
                                    }{" "}
                                    verified
                                </strong>{" "}
                                of {pharmacy.pharmacists.length}
                            </p>
                            <p className="text-muted-foreground">
                                At least one valid verified license is required
                                before approving a pharmacy.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Dialog
                open={licenseDialogOpen}
                onOpenChange={setLicenseDialogOpen}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {selectedLicense ? "Edit license" : "Add license"}
                        </DialogTitle>
                        <DialogDescription>
                            Upload a PDF or image. Documents are private and
                            available to admins only.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitLicense} className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="license_number">
                                License number
                            </Label>
                            <Input
                                id="license_number"
                                value={licenseForm.data.license_number}
                                onChange={(event) =>
                                    licenseForm.setData(
                                        "license_number",
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            {errors.license_number && (
                                <p className="text-sm text-destructive">
                                    {errors.license_number}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="license_type">License type</Label>
                            <Input
                                id="license_type"
                                value={licenseForm.data.license_type}
                                onChange={(event) =>
                                    licenseForm.setData(
                                        "license_type",
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            {errors.license_type && (
                                <p className="text-sm text-destructive">
                                    {errors.license_type}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="issued_date">Issued date</Label>
                                <Input
                                    id="issued_date"
                                    type="date"
                                    value={licenseForm.data.issued_date}
                                    onChange={(event) =>
                                        licenseForm.setData(
                                            "issued_date",
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="expiry_date">Expiry date</Label>
                                <Input
                                    id="expiry_date"
                                    type="date"
                                    value={licenseForm.data.expiry_date}
                                    onChange={(event) =>
                                        licenseForm.setData(
                                            "expiry_date",
                                            event.target.value,
                                        )
                                    }
                                />
                                {errors.expiry_date && (
                                    <p className="text-sm text-destructive">
                                        {errors.expiry_date}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="license_document">
                                {selectedLicense
                                    ? "Replace document (optional)"
                                    : "License document"}
                            </Label>
                            <Input
                                id="license_document"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={(event) =>
                                    licenseForm.setData(
                                        "document",
                                        event.currentTarget.files?.[0] ?? null,
                                    )
                                }
                            />
                            {errors.document && (
                                <p className="text-sm text-destructive">
                                    {errors.document}
                                </p>
                            )}
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                disabled={licenseForm.processing}
                            >
                                {licenseForm.processing
                                    ? "Saving…"
                                    : "Save license"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={pharmacistDialogOpen}
                onOpenChange={setPharmacistDialogOpen}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {selectedPharmacist
                                ? "Edit pharmacist"
                                : "Add pharmacist"}
                        </DialogTitle>
                        <DialogDescription>
                            Pharmacist registration documents are stored
                            privately.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitPharmacist} className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="pharmacist_name">Name</Label>
                            <Input
                                id="pharmacist_name"
                                value={pharmacistForm.data.name}
                                onChange={(event) =>
                                    pharmacistForm.setData(
                                        "name",
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            {pharmacistErrors.name && (
                                <p className="text-sm text-destructive">
                                    {pharmacistErrors.name}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="registration_number">
                                Registration number
                            </Label>
                            <Input
                                id="registration_number"
                                value={pharmacistForm.data.registration_number}
                                onChange={(event) =>
                                    pharmacistForm.setData(
                                        "registration_number",
                                        event.target.value,
                                    )
                                }
                            />
                            {pharmacistErrors.registration_number && (
                                <p className="text-sm text-destructive">
                                    {pharmacistErrors.registration_number}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="pharmacist_phone">Phone</Label>
                                <Input
                                    id="pharmacist_phone"
                                    value={pharmacistForm.data.phone}
                                    onChange={(event) =>
                                        pharmacistForm.setData(
                                            "phone",
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="pharmacist_email">Email</Label>
                                <Input
                                    id="pharmacist_email"
                                    type="email"
                                    value={pharmacistForm.data.email}
                                    onChange={(event) =>
                                        pharmacistForm.setData(
                                            "email",
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="qualification">Qualification</Label>
                            <Input
                                id="qualification"
                                value={pharmacistForm.data.qualification}
                                onChange={(event) =>
                                    pharmacistForm.setData(
                                        "qualification",
                                        event.target.value,
                                    )
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="pharmacist_document">
                                {selectedPharmacist
                                    ? "Replace document (optional)"
                                    : "Registration document"}
                            </Label>
                            <Input
                                id="pharmacist_document"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={(event) =>
                                    pharmacistForm.setData(
                                        "registration_document",
                                        event.currentTarget.files?.[0] ?? null,
                                    )
                                }
                            />
                            {pharmacistErrors.registration_document && (
                                <p className="text-sm text-destructive">
                                    {pharmacistErrors.registration_document}
                                </p>
                            )}
                        </div>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={pharmacistForm.data.is_primary}
                                onChange={(event) =>
                                    pharmacistForm.setData(
                                        "is_primary",
                                        event.target.checked,
                                    )
                                }
                            />{" "}
                            Primary pharmacist
                        </label>
                        <DialogFooter>
                            <Button
                                type="submit"
                                disabled={pharmacistForm.processing}
                            >
                                {pharmacistForm.processing
                                    ? "Saving…"
                                    : "Save pharmacist"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={reviewTarget !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setReviewTarget(null);
                        rejectionForm.reset();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            Reject {reviewTarget?.type ?? "record"}
                        </DialogTitle>
                        <DialogDescription>
                            Provide a clear reason so the pharmacy owner knows
                            what to correct.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitRejection} className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="rejection_reason">
                                Rejection reason
                            </Label>
                            <Textarea
                                id="rejection_reason"
                                value={rejectionForm.data.rejection_reason}
                                onChange={(event) =>
                                    rejectionForm.setData(
                                        "rejection_reason",
                                        event.target.value,
                                    )
                                }
                                required
                                maxLength={2000}
                                rows={4}
                            />
                            {rejectionForm.errors.rejection_reason && (
                                <p className="text-sm text-destructive">
                                    {rejectionForm.errors.rejection_reason}
                                </p>
                            )}
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={
                                    rejectionForm.processing ||
                                    statusForm.processing
                                }
                            >
                                Submit rejection
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
