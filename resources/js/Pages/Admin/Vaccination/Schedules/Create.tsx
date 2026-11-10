import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import ScheduleForm from '@/Components/vaccination/ScheduleForm';
import type { VaccineOption } from '@/Components/vaccination/types';

export default function Create({ vaccines }: { vaccines: VaccineOption[] }) {
    return (
        <AppLayout>
            <Head title="Create Vaccination Schedule" />
            <div className="mx-auto w-full space-y-6 p-4 md:p-6">
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" asChild><Link href={route('vaccination-schedules.index')}><ArrowLeft className="h-4 w-4" /></Link></Button>
                    <div><h1 className="text-2xl font-semibold">Create vaccination schedule</h1><p className="text-sm text-muted-foreground">Configure a vaccine dose and its recommended timing.</p></div>
                </div>
                <div className="rounded-lg border bg-card p-5 md:p-6">
                    <ScheduleForm vaccines={vaccines} />
                </div>
            </div>
        </AppLayout>
    );
}
