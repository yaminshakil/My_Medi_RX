import { Button } from '@/Components/ui/button';
import dayjs from 'dayjs';
import { Clock } from 'lucide-react';

export default function AvailableTimeSlot({ data, setData, selectedChamber, appointments }) {
    const bookedMap = appointments.reduce((acc, appt) => {
        // Make sure appointment_time is in HH:mm:ss
        const time24 = dayjs(`${appt.appointment_date} ${appt.appointment_time}`, 'YYYY-MM-DD HH:mm').format('HH:mm:ss');
        const key = `${appt.chamber_id}_${appt.appointment_date}_${time24}`;
        acc[key] = true;
        return acc;
    }, {});

    const getSchedulesForDay = (chamber, date) => {
        const day = dayjs(date).day();
        return chamber.schedules.filter((s) => Number(s.day) === day);
    };

    const generateTimeSlots = (date, start, end, step) => {
        const slots = [];
        let current = dayjs(`${date} ${start}`, 'YYYY-MM-DD HH:mm');
        const last = dayjs(`${date} ${end}`, 'YYYY-MM-DD HH:mm');

        while (current.isBefore(last) || current.isSame(last)) {
            slots.push({
                display: current.format('hh:mm A'), // for UI
                value: current.format('HH:mm:ss'), // for bookedMap comparison & DB
            });
            current = current.add(step, 'minute');
        }
        return slots;
    };

    return (
        <div className="mb-6 rounded-xl bg-white p-4 shadow">
            <h3 className="mb-3 text-lg font-semibold">Available Slots on {dayjs(data.appointment_date).format('DD MMM, YYYY')}</h3>

            {getSchedulesForDay(selectedChamber, data.appointment_date).map((schedule, idx) => (
                <div key={idx} className="mb-4">
                    <h2 className="mb-2 text-lg font-bold text-gray-600">
                        {dayjs(`${dayjs(data.appointment_date).format('YYYY-MM-DD')} ${schedule.start_time}`, 'YYYY-MM-DD HH:mm').format('h:mm A')} –{' '}
                        {dayjs(`${dayjs(data.appointment_date).format('YYYY-MM-DD')} ${schedule.end_time}`, 'YYYY-MM-DD HH:mm').format('h:mm A')}
                    </h2>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        {generateTimeSlots(data.appointment_date, schedule.start_time, schedule.end_time, Number(schedule.slot_duration)).map(
                            (slot, i) => {
                                const key = `${selectedChamber.id}_${data.appointment_date}_${slot.value}`;
                                const isBooked = bookedMap[key] || false; // fallback to false
                                // Disable if booked OR already passed (today only)
                                const slotDateTime = dayjs(`${data.appointment_date} ${slot.value}`, 'YYYY-MM-DD HH:mm:ss');
                                const isPast = dayjs(data.appointment_date).isSame(dayjs(), 'day') && slotDateTime.isBefore(dayjs());

                                const disabled = isBooked || isPast;

                                return (
                                    <Button
                                        key={i}
                                        type="button"
                                        onClick={() => !isBooked && setData('appointment_time', slot.value)}
                                        disabled={disabled}
                                        className={`flex items-center justify-center rounded-lg border px-4 py-2 text-sm ${disabled
                                                ? 'cursor-not-allowed bg-gray-300 text-gray-500'
                                                : data.appointment_time === slot.value
                                                    ? 'text-white'
                                                    : 'bg-gray-100 text-black hover:bg-blue-100'
                                            }`}
                                    >
                                        {data.appointment_time === slot.value ? (
                                            <Clock className="mr-4 cursor-pointer text-white" size={20} />
                                        ) : (
                                            <Clock className="mr-4 cursor-pointer text-[var(--base-color)]" size={20} />
                                        )}{' '}
                                        <span>{slot.display}</span>
                                    </Button>
                                );
                            },
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
}
