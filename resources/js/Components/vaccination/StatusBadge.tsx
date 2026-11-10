import { Badge } from '@/components/ui/badge';
import type { VaccinationRecord } from './types';

const statusStyles: Record<VaccinationRecord['status'], string> = {
    scheduled: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300',
    completed: 'border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-300',
    missed: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    cancelled: 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300',
};

export default function StatusBadge({ status }: { status: VaccinationRecord['status'] }) {
    return (
        <Badge variant="outline" className={statusStyles[status]}>
            {status.charAt(0).toUpperCase() + status.slice(1)}
        </Badge>
    );
}
