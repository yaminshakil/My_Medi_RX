import { Button } from '@/Components/ui/button';
import { closestCenter, DndContext, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { GripVertical, Trash2 } from 'lucide-react';

import { arrayMove, SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';

import { CSS } from '@dnd-kit/utilities';

export default function MedicationsList({ medications, onReorder, setEditingIndex, setEditingMedication, handleMedicineRemove }) {
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 5 },
        }),
    );

    function handleDragEnd(event) {
        const { active, over } = event;

        if (!over || active.id === over.id) return;

        const oldIndex = medications.findIndex((m, index) => index === active.id);
        const newIndex = medications.findIndex((m, index) => index === over.id);

        const newList = arrayMove(medications, oldIndex, newIndex);
        onReorder(newList);
    }

    return (
        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
            <SortableContext items={medications.map((_, index) => index)} strategy={verticalListSortingStrategy}>
                <div className="mt-3 space-y-3">
                    {medications.map((med, idx) => (
                        <SortableItem
                            key={idx}
                            id={idx}
                            idx={idx}
                            med={med}
                            setEditingIndex={setEditingIndex}
                            setEditingMedication={setEditingMedication}
                            handleMedicineRemove={handleMedicineRemove}
                        />
                    ))}
                </div>
            </SortableContext>
        </DndContext>
    );
}

function SortableItem({ id, idx, med, setEditingIndex, setEditingMedication, handleMedicineRemove }) {
    const { attributes, listeners, setNodeRef, transform, transition } = useSortable({ id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    return (
        <div ref={setNodeRef} style={style} className="relative rounded-xl border bg-white p-4 shadow-md">
            <div className="flex items-start justify-between">
                <div className="flex items-center gap-2">
                    {/* Drag Handle */}
                    <button {...attributes} {...listeners} className="cursor-grab text-gray-400 hover:text-gray-600">
                        <GripVertical size={20} />
                    </button>

                    <div>
                        <h3 className="font-bold text-[var(--base-color)]">
                            {idx + 1}. {med.brand_name || 'Unnamed Medicine'}
                        </h3>
                        <p className="text-sm text-gray-600">
                            <span className="font-semibold">{med.type || 'Type'}</span> • {med.strength || 'N/A'}
                        </p>
                        <p className="text-sm">
                            <strong>Dosage:</strong> {med.dosage || '-'} • <strong>When to Eat:</strong> {med.meal_time || '-'} •{' '}
                            <strong>Duration:</strong> {med.duration || '-'}
                        </p>
                        {med.advice && <p className="text-sm text-gray-700 italic">{med.advice}</p>}
                    </div>
                </div>

                <div className="flex space-x-2">
                    {/* Edit Button */}
                    <Button
                        type="button"
                        className="rounded-lg text-sm text-white"
                        onClick={() => {
                            setEditingIndex(idx);
                            setEditingMedication({ ...med });
                        }}
                    >
                        Edit
                    </Button>

                    {/* Delete Button—disabled for first item */}
                    {idx !== 0 && (
                        <Button variant="destructive" type="button" className="text-sm text-white" onClick={() => handleMedicineRemove(idx)}>
                            <Trash2 size={16} />
                        </Button>
                    )}
                </div>
            </div>
        </div>
    );
}
