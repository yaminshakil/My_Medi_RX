import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';


interface Props {
    hospitalUuid: string;
    documentTypes: Record<string, string>;
}

export default function VerificationDocumentUpload({
    hospitalUuid,
    documentTypes,
}: Props) {
    const { data, setData, post, processing, errors, reset } =
        useForm({
            document_type: '',
            document_title: '',
            document_number: '',
            document: null as File | null,
            issued_at: '',
            expires_at: '',
        });

    const submit = (
        e: React.FormEvent
    ) => {
        e.preventDefault();

        post(
            route(
                'hospital.verification.documents.store',
                hospitalUuid
            ),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => reset(),
            }
        );
    };

    return (
        <form
            onSubmit={submit}
            className="space-y-4"
        >
            <div>
                <label className="text-sm font-medium">
                    Document Type
                </label>

                <select
                    value={data.document_type}
                    onChange={(e) =>
                        setData(
                            'document_type',
                            e.target.value
                        )
                    }
                    className="mt-1 w-full rounded-md border p-2"
                >
                    <option value="">
                        Select document
                    </option>

                    {Object.entries(documentTypes).map(
                        ([value, label]) => (
                            <option
                                key={value}
                                value={value}
                            >
                                {label}
                            </option>
                        )
                    )}
                </select>

                {errors.document_type && (
                    <p className="text-sm text-destructive">
                        {errors.document_type}
                    </p>
                )}
            </div>

            <div>
                <label className="text-sm font-medium">
                    Document Number
                </label>

                <input
                    value={data.document_number}
                    onChange={(e) =>
                        setData(
                            'document_number',
                            e.target.value
                        )
                    }
                    className="mt-1 w-full rounded-md border p-2"
                />
            </div>

            <div>
                <label className="text-sm font-medium">
                    Document
                </label>

                <input
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={(e) =>
                        setData(
                            'document',
                            e.target.files?.[0] ?? null
                        )
                    }
                    className="mt-1 w-full"
                />

                {errors.document && (
                    <p className="text-sm text-destructive">
                        {errors.document}
                    </p>
                )}
            </div>

            <div className="grid gap-4 md:grid-cols-2">

                <div>
                    <label className="text-sm font-medium">
                        Issued Date
                    </label>

                    <input
                        type="date"
                        value={data.issued_at}
                        onChange={(e) =>
                            setData(
                                'issued_at',
                                e.target.value
                            )
                        }
                        className="mt-1 w-full rounded-md border p-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium">
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        value={data.expires_at}
                        onChange={(e) =>
                            setData(
                                'expires_at',
                                e.target.value
                            )
                        }
                        className="mt-1 w-full rounded-md border p-2"
                    />
                </div>

            </div>

            <Button
                type="submit"
                disabled={processing}
            >
                {processing
                    ? 'Uploading...'
                    : 'Upload Document'}
            </Button>
        </form>
    );
}
