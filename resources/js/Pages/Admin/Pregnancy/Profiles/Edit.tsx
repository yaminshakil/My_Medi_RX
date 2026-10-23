
import { Head, Link } from "@inertiajs/react";
import AppLayout from '@/Layouts/app-layout';
import { Button } from "@/Components/ui/button";
import ProfileForm from "./Form";
import { type BreadcrumbItem } from '@/types';

type Profile = {
    id: number;
    user_id: number;
    last_menstrual_period: string;
    dating_confirmed_date: string | null;
    is_active: boolean;
};

type Props = {
    profile: Profile;
    patients: {
        user_id: number;
        name: string;
        email: string | null;
    }[];
};

export default function Edit({ profile, patients }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Pregnancy Profile', href: '/admin/pregnancy/profiles' }];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Pregnancy Profile" />

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
                        Edit Pregnancy Profile
                    </h1>
                </div>

                <ProfileForm
                    profile={profile}
                    patients={patients}
                />
            </div>
        </AppLayout>
    );
}
