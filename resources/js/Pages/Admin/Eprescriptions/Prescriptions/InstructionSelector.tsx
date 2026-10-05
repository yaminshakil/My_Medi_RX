import { CommonModal } from '@/Components/CommonModal';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { closestCenter, DndContext, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import axios from 'axios';
import { GripVertical } from 'lucide-react';
import { useEffect, useState } from 'react';

// 🔸 Single sortable item component
function SortableInstructionItem({ inst, selectedInstructions, onSelect, onEdit, onDelete }) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: inst.id.toString(),
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const selected = selectedInstructions.includes(inst.text);

    return (
        <div
            ref={setNodeRef}
            style={style}
            {...attributes}
            className={`flex cursor-pointer items-center justify-between rounded-lg border p-2 ${selected ? 'border-green-500 bg-green-100' : 'hover:bg-gray-100'
                } ${isDragging ? 'bg-gray-200' : ''}`}
            onClick={() => onSelect(inst)}
        >
            <div className="flex items-center gap-2">
                <span {...listeners} className="cursor-grab rounded p-1 hover:bg-gray-200">
                    <GripVertical className="h-4 w-4 text-gray-500" />
                </span>
                <span>{inst.text}</span>
            </div>
            <div className="flex gap-2">
                <button
                    type="button"
                    className="text-blue-600 hover:underline"
                    onClick={(e) => {
                        e.stopPropagation();
                        onEdit(inst);
                    }}
                >
                    Edit
                </button>
                <button
                    type="button"
                    className="text-red-600 hover:underline"
                    onClick={(e) => {
                        e.stopPropagation();
                        onDelete(inst.id);
                    }}
                >
                    Delete
                </button>
            </div>
        </div>
    );
}

export default function InstructionSelector({ data, setData, selectedInstructions, setSelectedInstructions }) {
    const [showInstructionModal, setShowInstructionModal] = useState(false);
    const [instructionList, setInstructionList] = useState([]);
    const [newInstruction, setNewInstruction] = useState('');
    const [editId, setEditId] = useState(null);

    // Load instructions from API
    const loadInstructions = () => {
        axios.get('/admin/api/instructions').then((res) => setInstructionList(res.data));
    };

    useEffect(() => {
        loadInstructions();
    }, []);

    // ✅ Dnd-kit sensors
    const sensors = useSensors(useSensor(PointerSensor));

    // ✅ Handle drag end (reordering)
    const handleDragEnd = (event) => {
        const { active, over } = event;
        if (!over || active.id === over.id) return;

        const oldIndex = instructionList.findIndex((i) => i.id.toString() === active.id);
        const newIndex = instructionList.findIndex((i) => i.id.toString() === over.id);
        const reordered = arrayMove(instructionList, oldIndex, newIndex);
        setInstructionList(reordered);

        // Persist new order to backend
        axios.post('/admin/api/instructions/reorder', {
            order: reordered.map((item) => item.id),
        });
    };

    // Select/Deselect
    const handleSelectInstruction = (instruction) => {
        if (selectedInstructions.includes(instruction.text)) {
            setSelectedInstructions(selectedInstructions.filter((i) => i !== instruction.text));
        } else {
            setSelectedInstructions([...selectedInstructions, instruction.text]);
        }
    };

    // Sync to textarea
    useEffect(() => {
        setData('instructions', selectedInstructions.join('\n'));
    }, [selectedInstructions]);

    // Save / Update
    const handleSaveInstruction = async () => {
        const text = newInstruction.trim();
        console.log(text);
        if (!text) return;

        try {
            if (editId) {
                await axios.put(`/admin/api/instructions/${editId}`, {
                    text,
                });
            } else {
                await axios.post('/admin/api/instructions', {
                    text,
                });
            }

            setNewInstruction('');
            setEditId(null);
            loadInstructions();
        } catch (error) {
            console.error('Failed to save instruction:', error);
        }
    };

    const handleEditInstruction = (inst) => {
        setNewInstruction(inst.text);
        setEditId(inst.id);
    };

    const handleDeleteInstruction = (id) => {
        if (confirm('Are you sure you want to delete this instruction?')) {
            axios.delete(`/admin/api/instructions/${id}`).then(() => loadInstructions());
        }
    };

    return (
        <div>
            {/* Advice Notes */}
            <div>
                <label className="mb-1 block text-sm font-medium">Advice Notes</label>
                <div className="flex gap-2">
                    <Textarea
                        className="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        rows={3}
                        value={data.instructions}
                        onChange={(e) => {
                            setData('instructions', e.target.value);
                            setSelectedInstructions(
                                e.target.value
                                    .split('\n')
                                    .map((l) => l.trim())
                                    .filter(Boolean),
                            );
                        }}
                        placeholder="Advice notes..."
                    />
                    <Button type="button" className="cursor-pointer rounded-lg px-4 text-white" onClick={() => setShowInstructionModal(true)}>
                        + Select
                    </Button>
                </div>

                {/* Selected Chips */}
                {selectedInstructions.length > 0 && (
                    <div className="mt-2 flex flex-wrap gap-2">
                        {selectedInstructions.map((inst, idx) => (
                            <span
                                key={idx}
                                className="flex items-center rounded-full border border-green-400 bg-green-100 px-2 py-1 text-sm text-green-700"
                            >
                                {inst}
                                <button
                                    type="button"
                                    className="ml-2 text-red-600 hover:text-red-800"
                                    onClick={() => setSelectedInstructions(selectedInstructions.filter((i) => i !== inst))}
                                >
                                    ✕
                                </button>
                            </span>
                        ))}
                    </div>
                )}
            </div>

            {/* Modal */}
            <CommonModal
                onConfirm={() => setShowInstructionModal(false)}
                isOpen={showInstructionModal}
                onClose={() => setShowInstructionModal(false)}
                title="Manage Advice"
                onCancel={() => setShowInstructionModal(false)}
            >
                {/* Add/Edit */}
                <div className="mb-4 flex gap-2">
                    <Input
                        type="text"
                        className="flex-1 rounded-lg border px-2 py-1"
                        placeholder="Enter new Advice..."
                        value={newInstruction}
                        onChange={(e) => setNewInstruction(e.target.value)}
                    />
                    <Button type="button" onClick={handleSaveInstruction}>
                        {editId ? 'Update' : 'Add'}
                    </Button>
                </div>

                {/* Sortable List */}
                <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                    <SortableContext items={instructionList.map((i) => i.id.toString())} strategy={verticalListSortingStrategy}>
                        <div className="max-h-64 space-y-2 overflow-y-auto">
                            {instructionList.map((inst) => (
                                <SortableInstructionItem
                                    key={inst.id}
                                    inst={inst}
                                    selectedInstructions={selectedInstructions}
                                    onSelect={handleSelectInstruction}
                                    onEdit={handleEditInstruction}
                                    onDelete={handleDeleteInstruction}
                                />
                            ))}
                        </div>
                    </SortableContext>
                </DndContext>
            </CommonModal>
        </div>
    );
}
