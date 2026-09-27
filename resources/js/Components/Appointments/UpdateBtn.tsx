import { Button } from '@/Components/ui/button';
import { LoaderCircle } from 'lucide-react';

export default function UpdateBtn({ handleSubmit, processing }) {
    return (
        <form onSubmit={handleSubmit}>
            <Button type="submit" className="w-full px-6 py-3 text-white md:w-auto" disabled={processing}>
                {processing && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
                Update Appointment
            </Button>
        </form>
    );
}
