import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from 'antd';

export default function AddMedicine({
    editingMedication,
    setEditingMedication,
    medicines,
    medicine_doses,
    medicine_durations,
    mimealtimeOption,
    editingIndex,
    saveMedication,
}) {
    return (
        <div className="flex w-full flex-col items-center gap-2 md:flex-row">
            <div className="flex-1">
                <div className="grid grid-cols-1 gap-4 rounded-lg border p-4 shadow md:grid-cols-3">
                    <div className="w-full">
                        <Input
                            className="h-10 w-full"
                            placeholder="Type"
                            value={editingMedication.type}
                            onChange={(e) => setEditingMedication({ ...editingMedication, type: e.target.value })}
                        />
                    </div>
                    <div className="w-full">
                        <Select
                            allowClear
                            placeholder="Select Medicine"
                            className="!h-10 w-full"
                            showSearch
                            optionFilterProp="label"
                            value={editingMedication.medicine_id}
                            options={medicines}
                            onChange={(value) => {
                                const selected = medicines.find((m) => m.value === value);
                                setEditingMedication({
                                    ...editingMedication,
                                    medicine_id: value,
                                    brand_name: selected?.label || '',
                                    strength: selected?.strength || '',
                                    type: selected?.type || '',
                                });
                            }}
                        />
                    </div>
                    <div className="w-full">
                        <Input
                            className="h-10 w-full"
                            placeholder="Strength"
                            value={editingMedication.strength}
                            onChange={(e) => setEditingMedication({ ...editingMedication, strength: e.target.value })}
                        />
                    </div>
                    <div className="w-full">
                        <Select
                            allowClear
                            placeholder="Select Medicine Dosage"
                            className="!h-10 w-full"
                            showSearch
                            optionFilterProp="label"
                            value={editingMedication.dosage || undefined}
                            options={medicine_doses}
                            onChange={(value) => {
                                setEditingMedication({
                                    ...editingMedication,
                                    dosage: value,
                                });
                            }}
                        />
                    </div>
                    <div className="w-full">
                        <Select
                            allowClear
                            placeholder="Select Medicine Duration"
                            className="!h-10 w-full"
                            showSearch
                            optionFilterProp="label"
                            value={editingMedication.duration || undefined}
                            options={medicine_durations}
                            onChange={(value) => {
                                setEditingMedication({
                                    ...editingMedication,
                                    duration: value,
                                });
                            }}
                        />
                    </div>
                    <div className="w-full">
                        <Select
                            allowClear
                            placeholder="Select when to eat"
                            className="!h-10 w-full"
                            showSearch
                            optionFilterProp="label"
                            value={editingMedication.meal_time || undefined}
                            options={mimealtimeOption}
                            onChange={(value) => {
                                setEditingMedication({
                                    ...editingMedication,
                                    meal_time: value,
                                });
                            }}
                        />
                    </div>
                    <div className="col-span-3">
                        <Input
                            className="h-10 w-full"
                            placeholder="Advice"
                            value={editingMedication.advice}
                            onChange={(e) => setEditingMedication({ ...editingMedication, advice: e.target.value })}
                        />
                    </div>
                </div>
            </div>
            <Button type="button" className="mb-2 cursor-pointer text-white" onClick={saveMedication}>
                {editingIndex !== null ? 'Update Medication' : '+ Add Medication'}
            </Button>
        </div>
    );
}
