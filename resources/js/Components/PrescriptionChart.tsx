// PrescriptionChart.jsx
import React from "react";
import {
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from "recharts";

/**
 * Props:
 *  - data: array of { date: 'YYYY-MM-DD', doctor_5: 5, doctor_8: 2, ... }
 *  - doctorsMap: { doctor_5: 'Dr. Ali', doctor_8: 'Dr. Ali' }
 */
export default function PrescriptionChart({ data = [], doctorsMap = {} }) {
    // Normalize & sort incoming data by date
    const chartData = React.useMemo(() => {
        if (!Array.isArray(data) || data.length === 0) return [];
        return [...data]
            .map((d) => ({ ...d, date: String(d.date) }))
            .sort((a, b) => new Date(a.date) - new Date(b.date));
    }, [data]);

    // Use doctorsMap keys (keeps order stable and prevents collapsing on identical display names)
    const doctorKeys = React.useMemo(
        () => Object.keys(doctorsMap || {}),
        [doctorsMap],
    );

    if (chartData.length === 0 || doctorKeys.length === 0) {
        return (
            <div className="p-4 text-sm text-gray-500">
                No prescription data to display.
            </div>
        );
    }

    return (
        <div style={{ width: "100%", height: 250 }}>
            <ResponsiveContainer width="100%" height="100%">
                <LineChart data={chartData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="date" />
                    <YAxis />
                    <Tooltip />
                    <Legend />
                    {doctorKeys.map((key, i) => (
                        <Line
                            key={key}
                            type="monotone"
                            dataKey={key}
                            name={doctorsMap[key] || key} // friendly name in legend
                            stroke={`hsl(${(i * 73) % 360}, 65%, 45%)`}
                            strokeWidth={2}
                            dot={false}
                        />
                    ))}
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
}
