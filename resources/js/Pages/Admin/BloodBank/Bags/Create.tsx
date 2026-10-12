import { useMemo } from 'react';
import { useForm } from '@inertiajs/react';
import { Droplet, CalendarClock, Package, ShieldCheck } from 'lucide-react';

import BloodBankLayout from '@/Components/BloodBank/BloodBankLayout';

import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

interface Option {
    value: string;
    label: string;
    shelf_life_days?: number;
}

interface CreateProps {
    options: {
        groups: Option[];
        components: Option[];
        storage_units: Option[];
    };
}

export default function Create({ options }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        bag_number: '',
        batch_number: '',
        blood_group: '',
        component: '',
        volume_ml: '',
        collected_at: '',
        expires_at: '',
        storage_unit_id: '',
        quarantine: false,
    });

    const component = options.components.find(
        (item) => item.value === data.component
    );

    const suggestedExpiry = useMemo(() => {
        if (!component || !data.collected_at || data.expires_at) {
            return null;
        }

        const collectedAt = new Date(data.collected_at);

        if (Number.isNaN(collectedAt.getTime())) {
            return null;
        }

        collectedAt.setDate(
            collectedAt.getDate() + (component.shelf_life_days ?? 0)
        );

        return collectedAt;
    }, [component, data.collected_at, data.expires_at]);

    const formatDateTime = (date: Date) =>
        new Intl.DateTimeFormat(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route('blood-bank.bags.store'));
    };

    const fieldError = (name: keyof typeof errors) =>
        errors[name] ? (
            <p className="text-sm font-medium text-destructive" role="alert">
                {errors[name]}
            </p>
        ) : null;

    console.log(options.groups);
    return (
        <BloodBankLayout title="Register blood unit">
            <form onSubmit={submit}>
                <Card className="mx-auto w-full max-w-5xl">
                    <CardHeader>
                        <div className="flex items-center gap-3">
                            <div className="flex size-12 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                <Droplet className="size-6" />
                            </div>

                            <div>
                                <CardTitle>Register blood unit</CardTitle>
                                <CardDescription className="mt-1">
                                    Record a collected blood unit, its storage
                                    location, and expiry information.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-8">
                        <section className="space-y-4">
                            <div className="flex items-center gap-2 border-b pb-3">
                                <Package className="size-5 text-muted-foreground" />
                                <h3 className="font-semibold">
                                    Blood unit information
                                </h3>
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="bag_number">
                                        Bag number <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="bag_number"
                                        value={data.bag_number}
                                        onChange={(e) =>
                                            setData('bag_number', e.target.value)
                                        }
                                        placeholder="Enter barcode or ISBT number"
                                        aria-invalid={!!errors.bag_number}
                                        required
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Barcode or ISBT number printed on the bag.
                                    </p>
                                    {fieldError('bag_number')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="batch_number">
                                        Batch number <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="batch_number"
                                        value={data.batch_number}
                                        onChange={(e) =>
                                            setData('batch_number', e.target.value)
                                        }
                                        placeholder="Enter batch number"
                                        aria-invalid={!!errors.batch_number}
                                        required
                                    />
                                    {fieldError('batch_number')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="blood_group">
                                        Blood group <span className="text-destructive">*</span>
                                    </Label>
                                    <Select
                                        value={data.blood_group || undefined}
                                        onValueChange={(value) =>
                                            setData('blood_group', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="blood_group"
                                            aria-invalid={!!errors.blood_group}
                                        >
                                            <SelectValue placeholder="Select blood group" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {options.groups.map((group) => (
                                                <SelectItem
                                                    key={group.value}
                                                    value={group.value}
                                                >
                                                    {group.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {fieldError('blood_group')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="component">
                                        Blood component <span className="text-destructive">*</span>
                                    </Label>
                                    <Select
                                        value={data.component || undefined}
                                        onValueChange={(value) =>
                                            setData('component', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="component"
                                            aria-invalid={!!errors.component}
                                        >
                                            <SelectValue placeholder="Select component" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {options.components.map((item) => (
                                                <SelectItem
                                                    key={item.value}
                                                    value={item.value}
                                                >
                                                    {item.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {fieldError('component')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="volume_ml">
                                        Volume (ml) <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="volume_ml"
                                        type="number"
                                        min={10}
                                        max={1000}
                                        value={data.volume_ml}
                                        onChange={(e) =>
                                            setData('volume_ml', e.target.value)
                                        }
                                        placeholder="e.g. 450"
                                        aria-invalid={!!errors.volume_ml}
                                        required
                                    />
                                    {fieldError('volume_ml')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="storage_unit_id">
                                        Storage unit <span className="text-destructive">*</span>
                                    </Label>
                                    <Select
                                        value={data.storage_unit_id || undefined}
                                        onValueChange={(value) =>
                                            setData('storage_unit_id', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="storage_unit_id"
                                            aria-invalid={!!errors.storage_unit_id}
                                        >
                                            <SelectValue placeholder="Select storage unit" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {options.storage_units.map((unit) => (
                                                <SelectItem
                                                    key={unit.value}
                                                    value={unit.value}
                                                >
                                                    {unit.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {fieldError('storage_unit_id')}
                                </div>
                            </div>
                        </section>

                        <section className="space-y-4">
                            <div className="flex items-center gap-2 border-b pb-3">
                                <CalendarClock className="size-5 text-muted-foreground" />
                                <h3 className="font-semibold">
                                    Collection and expiry
                                </h3>
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="collected_at">
                                        Collected at <span className="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="collected_at"
                                        type="datetime-local"
                                        value={data.collected_at}
                                        onChange={(e) =>
                                            setData('collected_at', e.target.value)
                                        }
                                        aria-invalid={!!errors.collected_at}
                                        required
                                    />
                                    {fieldError('collected_at')}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="expires_at">Expires at</Label>
                                    <Input
                                        id="expires_at"
                                        type="datetime-local"
                                        value={data.expires_at}
                                        onChange={(e) =>
                                            setData('expires_at', e.target.value)
                                        }
                                        aria-invalid={!!errors.expires_at}
                                    />

                                    <p className="text-xs text-muted-foreground">
                                        {suggestedExpiry
                                            ? `Suggested expiry: ${formatDateTime(suggestedExpiry)}. Leave empty to use the server's default shelf life.`
                                            : 'Leave empty to use the standard shelf life for the selected component.'}
                                    </p>
                                    {fieldError('expires_at')}
                                </div>
                            </div>
                        </section>

                        <section className="rounded-lg border p-4">
                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="quarantine"
                                    checked={data.quarantine}
                                    onCheckedChange={(checked) =>
                                        setData('quarantine', checked === true)
                                    }
                                />

                                <div className="grid gap-1">
                                    <Label
                                        htmlFor="quarantine"
                                        className="flex items-center gap-2 font-medium"
                                    >
                                        <ShieldCheck className="size-4 text-rose-600" />
                                        Hold in quarantine
                                    </Label>

                                    <p className="text-sm text-muted-foreground">
                                        Keep this unit on hold until screening
                                        results are available. Quarantined units
                                        must not be issued for transfusion.
                                    </p>
                                </div>
                            </div>
                        </section>
                    </CardContent>

                    <CardFooter className="flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processing}
                            onClick={() =>
                                (window.location.href = route('blood-bank.bags.index'))
                            }
                        >
                            Cancel
                        </Button>

                        <Button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-rose-700 text-white hover:bg-rose-800 sm:w-auto"
                        >
                            <Droplet className="mr-2 size-4" />
                            {processing ? 'Registering...' : 'Register blood unit'}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </BloodBankLayout>
    );
}
