
import { Head, Link } from "@inertiajs/react";
import AppLayout from '@/Layouts/app-layout';
import { Button } from "@/Components/ui/button";
import ProfileForm from "./Form";

type Props = {
    patients: {
        user_id: number;
        name: string;
        email: string | null;
    }[];
};

export default function Create({ patients }: Props) {
    return (
        <AppLayout>
            <Head title="Create Pregnancy Profile" />

            <div className="space-y-6 p-6">
                <div>
                    <Button variant="outline" asChild>
                        <Link
                            href={route(
                                "admin.pregnancy.profiles.index"
                            )}
                        >
                            Back to Profiles
                        </Link>
                    </Button>

                    <h1 className="mt-4 text-2xl font-bold">
                        Create Pregnancy Profile
                    </h1>
                </div>

                <ProfileForm patients={patients} />
            </div>
        </AppLayout>
    );
}
