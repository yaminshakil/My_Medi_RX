import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

import AppLayout from '@/Layouts/app-layout';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';

import { Button } from '@/Components/ui/button';

import {
    BadgeCheck,
    CheckCircle,
    FileCheck,
    FileText,
    ShieldAlert,
    XCircle,
} from 'lucide-react';

interface Hospital {
    id: number;
    uuid: string;
    hospital_name: string;
    email?: string;
    mobile_number: string;
    address: string;

    verification_status: string;
    verification_note?: string;

    hospital_type?: {
        id: number;
        name: string;
    };

    verification_documents?: VerificationDocument[];
}

interface VerificationDocument {
    id: number;
    document_type: string;
    document_type_label: string;
    document_title?: string;
    document_number?: string;
    document_url: string;

    issued_at?: string;
    expires_at?: string;

    verification_status: string;
    rejection_reason?: string;
}

interface Props {
    hospital: Hospital;
    documentTypes: Record<string, string>;
}

export default function Verification({
    hospital,
    documentTypes,
}: Props) {
    const [rejectingDocument, setRejectingDocument] =
        useState<number | null>(null);

    const [rejectionReason, setRejectionReason] =
        useState('');

    const approveDocument = (id: number) => {
        router.post(
            route(
                'hospital.verification.documents.approve',
                id
            ),
            {},
            {
                preserveScroll: true,
            }
        );
    };

    const rejectDocument = (id: number) => {
        router.post(
            route(
                'hospital.verification.documents.reject',
                id
            ),
            {
                rejection_reason: rejectionReason,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setRejectingDocument(null);
                    setRejectionReason('');
                },
            }
        );
    };

    const approveHospital = () => {
        router.post(
            route(
                'hospital.verification.approve',
                hospital.uuid
            ),
            {},
            {
                preserveScroll: true,
            }
        );
    };

    const rejectHospital = () => {
        const note = window.prompt(
            'Enter rejection reason'
        );

        if (!note) {
            return;
        }

        router.post(
            route(
                'hospital.verification.reject',
                hospital.uuid
            ),
            {
                verification_note: note,
            },
            {
                preserveScroll: true,
            }
        );
    };

    return (
        <AppLayout>
            <Head title="Hospital Verification" />

            <div className="space-y-6 p-6">

                <Card>
                    <CardHeader>
                        <CardTitle>
                            Hospital Verification
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                Hospital Name
                            </p>

                            <p className="font-medium">
                                {hospital.hospital_name}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-muted-foreground">
                                Hospital Type
                            </p>

                            <p className="font-medium">
                                {hospital.hospital_type?.name ?? '-'}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-muted-foreground">
                                Mobile
                            </p>

                            <p>
                                {hospital.mobile_number}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-muted-foreground">
                                Email
                            </p>

                            <p>
                                {hospital.email ?? '-'}
                            </p>
                        </div>

                        <div className="md:col-span-2">
                            <p className="text-sm text-muted-foreground">
                                Address
                            </p>

                            <p>
                                {hospital.address}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            Verification Documents
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-4">

                        {hospital.verification_documents?.map(
                            (document) => (
                                <div
                                    key={document.id}
                                    className="rounded-lg border p-4"
                                >
                                    <div className="flex items-start justify-between gap-4">

                                        <div className="flex gap-3">

                                            <FileText className="mt-1 h-5 w-5" />

                                            <div>
                                                <h3 className="font-semibold">
                                                    {
                                                        document.document_type_label
                                                    }
                                                </h3>

                                                {document.document_number && (
                                                    <p className="text-sm text-muted-foreground">
                                                        No: {
                                                            document.document_number
                                                        }
                                                    </p>
                                                )}

                                                {document.expires_at && (
                                                    <p className="text-sm text-muted-foreground">
                                                        Expires: {
                                                            document.expires_at
                                                        }
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        <span className="rounded-full border px-3 py-1 text-xs">
                                            {
                                                document.verification_status
                                            }
                                        </span>
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2">

                                        <Button
                                            variant="outline"
                                            asChild
                                        >
                                            <a
                                                href={
                                                    document.document_url
                                                }
                                                target="_blank"
                                            >
                                                View Document
                                            </a>
                                        </Button>

                                        {document.verification_status ===
                                            'pending' && (
                                                <>
                                                    <Button
                                                        onClick={() =>
                                                            approveDocument(
                                                                document.id
                                                            )
                                                        }
                                                    >
                                                        <CheckCircle className="mr-2 h-4 w-4" />
                                                        Approve
                                                    </Button>

                                                    <Button
                                                        variant="destructive"
                                                        onClick={() =>
                                                            setRejectingDocument(
                                                                document.id
                                                            )
                                                        }
                                                    >
                                                        <XCircle className="mr-2 h-4 w-4" />
                                                        Reject
                                                    </Button>
                                                </>
                                            )}
                                    </div>

                                    {rejectingDocument ===
                                        document.id && (
                                            <div className="mt-4 space-y-3">
                                                <textarea
                                                    className="w-full rounded-md border p-3"
                                                    placeholder="Reason for rejection"
                                                    value={
                                                        rejectionReason
                                                    }
                                                    onChange={(e) =>
                                                        setRejectionReason(
                                                            e.target.value
                                                        )
                                                    }
                                                />

                                                <Button
                                                    variant="destructive"
                                                    onClick={() =>
                                                        rejectDocument(
                                                            document.id
                                                        )
                                                    }
                                                >
                                                    Confirm Rejection
                                                </Button>
                                            </div>
                                        )}
                                </div>
                            )
                        )}

                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            Final Verification
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="flex flex-wrap gap-3">

                        <Button
                            onClick={approveHospital}
                        >
                            <BadgeCheck className="mr-2 h-4 w-4" />
                            Approve Hospital
                        </Button>

                        <Button
                            variant="destructive"
                            onClick={rejectHospital}
                        >
                            <ShieldAlert className="mr-2 h-4 w-4" />
                            Reject Hospital
                        </Button>

                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}
