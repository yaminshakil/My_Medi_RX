import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';
import VaccinationRecordForm from '@/Components/vaccination/VaccinationRecordForm';
import type { VaccinationSchedule, VaccineOption } from '@/Components/vaccination/types';

type Props = { vaccines: VaccineOption[]; schedules: VaccinationSchedule[] };

export default function Create({ vaccines, schedules }: Props) {
    return (
        <AppLayout>
            <Head title="Add Vaccination" />
            <div className="mx-auto w-full space-y-6 p-4 md:p-6">
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" asChild><Link href={route('admin.vaccinations.index')}><ArrowLeft className="h-4 w-4" /></Link></Button>
                    <div><h1 className="text-2xl font-semibold">Add vaccination</h1><p className="text-sm text-muted-foreground">Record a scheduled or administered vaccine.</p></div>
                </div>
                <div className="rounded-lg border bg-card p-5 md:p-6">
                    <VaccinationRecordForm vaccines={vaccines} schedules={schedules} />
                </div>
            </div>
        </AppLayout>
    );
}
