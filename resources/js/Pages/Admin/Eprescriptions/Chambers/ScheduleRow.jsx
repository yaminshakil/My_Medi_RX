import { Button } from '@/components/ui/button';
import { Trash2 } from 'lucide-react';
const days = [
    { value: 6, label: 'Saturday' },
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
];

export default function ScheduleRow({ index, row, errors, handleChange, removeRow }) {
    return (
        <div className="grid grid-cols-1 gap-4 rounded border p-3 md:grid-cols-5">
            {/* Day */}
            <div className="min-w-0">
                <select value={row.day} onChange={(e) => handleChange(index, 'day', e.target.value)} className="w-full rounded border px-2 py-1">
                    <option value="">Select Day</option>
                    {days.map((d) => (
                        <option key={d} value={d.value}>
                            {d.label}
                        </option>
                    ))}
                </select>
                {errors?.[`schedules.${index}.day`] && <p className="text-sm text-red-600">{errors[`schedules.${index}.day`]}</p>}
            </div>
            <div className="min-w-0">
                {/* Start Time */}
                <input
                    type="time"
                    value={row.start_time}
                    onChange={(e) => handleChange(index, 'start_time', e.target.value)}
                    className="w-full rounded border px-2 py-1"
                />
                {errors?.[`schedules.${index}.start_time`] && <p className="text-sm text-red-600">{errors[`schedules.${index}.start_time`]}</p>}
            </div>
            <div className="min-w-0">
                {/* End Time */}
                <input
                    type="time"
                    value={row.end_time}
                    onChange={(e) => handleChange(index, 'end_time', e.target.value)}
                    className="w-full rounded border px-2 py-1"
                />
                {errors?.[`schedules.${index}.end_time`] && <p className="text-sm text-red-600">{errors[`schedules.${index}.end_time`]}</p>}
            </div>
            <div className="min-w-0">
                {/* Slot Duration */}
                <input
                    type="number"
                    value={row.slot_duration}
                    onChange={(e) => handleChange(index, 'slot_duration', e.target.value)}
                    className="w-full rounded border px-2 py-1"
                />
                {errors?.[`schedules.${index}.slot_duration`] && <p className="text-sm text-red-600">{errors[`schedules.${index}.slot_duration`]}</p>}
            </div>
            <div className="min-w-0">
                {/* Remove Button */}
                <Button variant="destructive" onClick={() => removeRow(index)} className="w-full border px-3 py-1">
                    <Trash2 className="mr-1 h-4 w-4" />
                    Remove
                </Button>
            </div>
        </div>
    );
}
