import dayjs from "dayjs";
import Calendar from "react-calendar";

export default function AppointmentCalendar({
    data,
    setData,
    selectedChamber,
}) {
    return (
        <div className="flex justify-center">
            <div className="w-full max-w-sm">
                <Calendar
                    value={
                        data.appointment_date
                            ? new Date(data.appointment_date)
                            : null
                    }
                    onChange={(date) =>
                        setData(
                            "appointment_date",
                            dayjs(date).format("YYYY-MM-DD"),
                        )
                    }
                    minDate={new Date()}
                    tileDisabled={({ date }) => {
                        const day = dayjs(date).day();
                        return !selectedChamber.schedules.some(
                            (s) => Number(s.day) === day,
                        );
                    }}
                    tileClassName={({ date, view }) => {
                        if (view === "month") {
                            const isSelected =
                                dayjs(date).format("YYYY-MM-DD") ===
                                data.appointment_date;

                            if (isSelected) {
                                return "custom-selected-date"; // add class
                            }
                        }
                        return null;
                    }}
                />
            </div>
        </div>
    );
}
