import { Button } from '@/Components/ui/button';
import { LoaderCircle } from 'lucide-react';
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/react';

export default function FormFooter({ handleCancel, processing, submitTitle }) {
    const { translations } = usePage<AppPageProps>().props;
    return (
        <div className="flex justify-start gap-2">
            <Button type="button" onClick={() => handleCancel()} className="mt-2 w-fit" disabled={processing}>
                {translations.common.cancel}
            </Button>
            <Button type="submit" className="mt-2 w-fit" disabled={processing}>
                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                {submitTitle}
            </Button>
        </div>
    );
}
