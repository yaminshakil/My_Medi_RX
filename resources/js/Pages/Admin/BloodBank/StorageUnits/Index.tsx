import { type ChangeEvent, type FormEvent, useState } from "react";
import { router, useForm } from "@inertiajs/react";
import BloodBankLayout from "@/Components/BloodBank/BloodBankLayout";
import {
    Btn,
    Card,
    EmptyState,
    Field,
    Pagination,
    Pill,
    inputCls,
    td,
    th,
} from "@/Components/BloodBank/ui";

type StorageUnit = {
    id: number;
    name: string;
    location: string | null;
    hospital_id: number | null;
    hospital_name: string | null;
    type: string;
    min_temp_c: string | number | null;
    max_temp_c: string | number | null;
    capacity: number | null;
    is_active: boolean;
    bags_count: number;
};

type StorageUnitForm = {
    name: string;
    location: string;
    hospital_id: string;
    type: string;
    min_temp_c: string;
    max_temp_c: string;
    capacity: string;
    is_active: boolean;
};

type TypeOption = {
    value: string;
    label: string;
};

type HospitalOption = {
    id: number;
    hospital_name: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PageProps = {
    storage_units: {
        data: StorageUnit[];
        links: PaginationLink[];
    };
    type_options: TypeOption[];
    hospitals: HospitalOption[];
};

const initialForm: StorageUnitForm = {
    name: "",
    location: "",
    hospital_id: "",
    type: "",
    min_temp_c: "",
    max_temp_c: "",
    capacity: "",
    is_active: true,
};

export default function Index({
    storage_units,
    type_options,
    hospitals,
}: PageProps) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<StorageUnitForm>(initialForm);

    const closeForm = () => {
        setFormOpen(false);
        setEditingId(null);
        reset();
        clearErrors();
    };

    const edit = (unit: StorageUnit) => {
        setEditingId(unit.id);
        setData({
            name: unit.name,
            location: unit.location ?? "",
            hospital_id: unit.hospital_id === null ? "" : String(unit.hospital_id),
            type: unit.type,
            min_temp_c: unit.min_temp_c === null ? "" : String(unit.min_temp_c),
            max_temp_c: unit.max_temp_c === null ? "" : String(unit.max_temp_c),
            capacity: unit.capacity === null ? "" : String(unit.capacity),
            is_active: unit.is_active,
        });
        clearErrors();
        setFormOpen(true);
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: closeForm,
        };

        if (editingId) {
            put(route("blood-bank.storage-units.update", editingId), options);
            return;
        }

        post(route("blood-bank.storage-units.store"), options);
    };

    const remove = (unit: StorageUnit) => {
        if (
            unit.bags_count > 0 ||
            !window.confirm(`Delete storage unit "${unit.name}"?`)
        ) {
            return;
        }

        router.delete(route("blood-bank.storage-units.destroy", unit.id), {
            preserveScroll: true,
        });
    };

    return (
        <BloodBankLayout
            title="Storage units"
            breadcrumbs={[
                { title: "Storage units", href: "/blood-bank/storage-units" },
            ]}
            actions={
                <Btn
                    href={undefined}
                    className={undefined}
                    onClick={() => {
                        if (formOpen && !editingId) {
                            closeForm();
                            return;
                        }

                        setEditingId(null);
                        setData(initialForm);
                        clearErrors();
                        setFormOpen(true);
                    }}
                >
                    {formOpen && !editingId ? "Close form" : "Add storage unit"}
                </Btn>
            }
        >
            {formOpen && (
                <form onSubmit={submit}>
                    <Card
                        title={
                            editingId ? "Edit storage unit" : "New storage unit"
                        }
                        actions={undefined}
                        className={undefined}
                    >
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <Field
                                label="Name"
                                error={errors.name}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <input
                                    className={inputCls}
                                    value={data.name}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) => setData("name", event.target.value)}
                                    required
                                />
                            </Field>
                            <Field
                                label="Location"
                                error={errors.location}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <input
                                    className={inputCls}
                                    value={data.location}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData("location", event.target.value)
                                    }
                                />
                            </Field>
                            <Field
                                label="Blood bank"
                                error={errors.hospital_id}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <select
                                    className={inputCls}
                                    value={data.hospital_id}
                                    onChange={(
                                        event: ChangeEvent<HTMLSelectElement>,
                                    ) =>
                                        setData(
                                            "hospital_id",
                                            event.target.value,
                                        )
                                    }
                                    required
                                >
                                    <option value="">Select a hospital</option>
                                    {hospitals.map((hospital) => (
                                        <option
                                            key={hospital.id}
                                            value={hospital.id}
                                        >
                                            {hospital.hospital_name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Type"
                                error={errors.type}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <select
                                    className={inputCls}
                                    value={data.type}
                                    onChange={(
                                        event: ChangeEvent<HTMLSelectElement>,
                                    ) => setData("type", event.target.value)}
                                    required
                                >
                                    <option value="">Select type</option>
                                    {type_options.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Minimum temperature (°C)"
                                error={errors.min_temp_c}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <input
                                    type="number"
                                    step="0.1"
                                    className={inputCls}
                                    value={data.min_temp_c}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData(
                                            "min_temp_c",
                                            event.target.value,
                                        )
                                    }
                                />
                            </Field>
                            <Field
                                label="Maximum temperature (°C)"
                                error={errors.max_temp_c}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <input
                                    type="number"
                                    step="0.1"
                                    className={inputCls}
                                    value={data.max_temp_c}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData(
                                            "max_temp_c",
                                            event.target.value,
                                        )
                                    }
                                />
                            </Field>
                            <Field
                                label="Capacity (units)"
                                error={errors.capacity}
                                hint={undefined}
                                className={undefined}
                                id={undefined}
                            >
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    className={inputCls}
                                    value={data.capacity}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData("capacity", event.target.value)
                                    }
                                />
                            </Field>
                            <label className="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(
                                        event: ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData(
                                            "is_active",
                                            event.target.checked,
                                        )
                                    }
                                />
                                Active and available for new blood units
                            </label>
                        </div>
                        <div className="mt-6 flex justify-end gap-3">
                            <Btn
                                variant="secondary"
                                href={undefined}
                                className={undefined}
                                onClick={closeForm}
                            >
                                Cancel
                            </Btn>
                            <Btn
                                type="submit"
                                href={undefined}
                                className={undefined}
                                disabled={processing}
                            >
                                {processing
                                    ? "Saving..."
                                    : editingId
                                      ? "Save changes"
                                      : "Create storage unit"}
                            </Btn>
                        </div>
                    </Card>
                </form>
            )}

            <Card
                className="overflow-hidden"
                title={undefined}
                actions={undefined}
            >
                {storage_units.data.length === 0 ? (
                    <EmptyState>
                        No storage units yet. Add one to start registering blood
                        units.
                    </EmptyState>
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className={th}>Name</th>
                                    <th className={th}>Location</th>
                                    <th className={th}>Blood bank</th>
                                    <th className={th}>Type</th>
                                    <th className={th}>Temperature range</th>
                                    <th className={th}>Capacity</th>
                                    <th className={th}>Blood bags</th>
                                    <th className={th}>Status</th>
                                    <th className={th}>Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {storage_units.data.map((unit: StorageUnit) => (
                                    <tr key={unit.id}>
                                        <td
                                            className={`${td} font-medium text-slate-900`}
                                        >
                                            {unit.name}
                                        </td>
                                        <td className={td}>
                                            {unit.location ?? "—"}
                                        </td>
                                        <td className={td}>
                                            {unit.hospital_name ?? "Unassigned"}
                                        </td>
                                        <td className={td}>
                                            {type_options.find(
                                                (option: TypeOption) =>
                                                    option.value === unit.type,
                                            )?.label ?? unit.type}
                                        </td>
                                        <td className={td}>
                                            {unit.min_temp_c ?? "—"} /{" "}
                                            {unit.max_temp_c ?? "—"} °C
                                        </td>
                                        <td className={td}>
                                            {unit.capacity ?? "—"}
                                        </td>
                                        <td className={td}>
                                            {unit.bags_count}
                                        </td>
                                        <td className={td}>
                                            <Pill
                                                tone={
                                                    unit.is_active
                                                        ? "teal"
                                                        : "slate"
                                                }
                                                className={undefined}
                                            >
                                                {unit.is_active
                                                    ? "Active"
                                                    : "Inactive"}
                                            </Pill>
                                        </td>
                                        <td className={td}>
                                            <div className="flex gap-3">
                                                <button
                                                    type="button"
                                                    onClick={() => edit(unit)}
                                                    className="text-sm font-medium text-rose-800 hover:underline"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => remove(unit)}
                                                    disabled={
                                                        unit.bags_count > 0
                                                    }
                                                    title={
                                                        unit.bags_count > 0
                                                            ? "Deactivate units with blood bags instead."
                                                            : undefined
                                                    }
                                                    className="text-sm font-medium text-rose-700 hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </Card>
            <Pagination links={storage_units.links} />
        </BloodBankLayout>
    );
}
