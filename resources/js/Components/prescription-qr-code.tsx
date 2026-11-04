
import { useState, useEffect } from "react";
import axios from "axios";

import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";

import {
    Download,
    Loader2,
    QrCode,
    RefreshCw,
} from "lucide-react";

import { toast } from "sonner";
import { usePage } from "@inertiajs/react";


interface PrescriptionQrCodeProps {
    prescriptionId: number;
    prescriptionNumber?: string;
    canGenerate?: boolean;
}

interface QrResponse {
    token: string;
    verification_url: string;
    qr_code: string;
    expires_at: string | null;
}

export default function PrescriptionQrCode({
    prescriptionId,
    prescriptionNumber,
    canGenerate = true,
}: PrescriptionQrCodeProps) {
    const [qrData, setQrData] = useState<QrResponse | null>(null);
    const [loading, setLoading] = useState(false);

    const { auth } = usePage<any>().props;
    const token = auth?.jwt_token;

    const generateQr = async () => {
        try {
            setLoading(true);

            const response = await axios.post(
                `/api/v1/prescriptions/${prescriptionId}/generate-qr`,
                {},
                {
                    headers: {
                        Authorization: `Bearer ${token}`, // Pass the prop token here
                    },
                }
            );

            setQrData(response.data.data);

            toast.success("Prescription QR generated successfully.");
        } catch (error: any) {
            console.error("Full Axios Error:", error.response || error); // Check this in F12 Console

            toast.error(
                error.response?.data?.message ||
                error.message ||
                "Unable to generate prescription QR."
            );
        } finally {
            setLoading(false);
        }
    };

    const downloadQr = () => {
        if (!qrData?.qr_code) return;

        const link = document.createElement("a");

        link.href = qrData.qr_code;
        link.download = `prescription-${prescriptionNumber || prescriptionId}-qr.svg`;

        document.body.appendChild(link);
        link.click();
        link.remove();
    };

    const fetchExistingQr = async () => {
    try {
        setLoading(true);

        const response = await axios.get(
            `/api/v1/prescriptions/${prescriptionId}/qr`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        );
        console.log("Existing QR fetched:", response.data.data);
        setQrData(response.data.data);
    } catch (error: any) {
        // No QR code exists yet.
        if (error.response?.status === 404) {
            setQrData(null);
        } else {
            console.error("Unable to load existing QR:", error);
        }
    } finally {
        setLoading(false);
    }
};

useEffect(() => {
    if (prescriptionId && token) {
        fetchExistingQr();
    }
}, [prescriptionId, token]);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <QrCode className="h-5 w-5" />
                    Prescription QR Code
                </CardTitle>
            </CardHeader>

            <CardContent className="flex flex-col items-center gap-4">

                {qrData ? (
                    <>
                        <img
                            src={qrData.qr_code}
                            alt="Prescription QR Code"
                            className="h-56 w-56 rounded-md border p-2"
                        />

                        <p className="text-center text-sm text-muted-foreground">
                            Scan this QR code to verify the prescription.
                        </p>

                        <p className="break-all text-center text-xs text-muted-foreground">
                            {qrData.verification_url}
                        </p>

                        <Button onClick={downloadQr}>
                            <Download className="mr-2 h-4 w-4" />
                            Download QR Code
                        </Button>
                    </>
                ) : (
                    <div className="flex flex-col items-center gap-3 py-6">
                        <QrCode className="h-16 w-16 text-muted-foreground" />

                        <p className="text-sm text-muted-foreground">
                            No QR code generated yet.
                        </p>

                        <Button
                            onClick={generateQr}
                            disabled={loading || !canGenerate}
                        >
                            {loading ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : (
                                <QrCode className="mr-2 h-4 w-4" />
                            )}

                            Generate QR Code
                        </Button>
                    </div>
                )}

                {qrData && canGenerate && (
                    <Button
                        variant="outline"
                        onClick={generateQr}
                        disabled={loading}
                    >
                        <RefreshCw className="mr-2 h-4 w-4" />
                        Refresh QR
                    </Button>
                )}
            </CardContent>
        </Card>
    );
}
