// GynaeHistorySidebar.jsx
import { X } from "lucide-react";

export default function GynaeHistorySidebar({ gynaeHistory, setGynaeHistory }) {
    if (!gynaeHistory) return null;

    const fields = [
        { label: "Marital Status", key: "marital_status" },
        { label: "Marriage Duration", key: "marriage_duration" },
        { label: "Consanguinity", key: "consanguinity" },
        { label: "Menarche Age", key: "menarche_age" },
        { label: "LMP", key: "lmp" },
        { label: "Cycle", key: "cycle" },
        { label: "Flow", key: "flow" },
        { label: "Dysmenorrhea", key: "dysmenorrhea", isBoolean: true },
        {
            label: "Contraceptive Use",
            key: "contraceptive_use",
            isBoolean: true,
        },
        { label: "Gravida", key: "gravida" },
        { label: "Para", key: "para" },
        { label: "Abortion", key: "abortion" },
        { label: "Living Children", key: "living_children" },
        { label: "EDD", key: "edd" },
        { label: "Antenatal Checkup", key: "anc" },
        { label: "Other History", key: "other_history" },
    ];

    const nonEmptyFields = fields.filter((field) => {
        const val = gynaeHistory[field.key];
        if (field.isBoolean) return val === true; // only include if true
        return val !== "" && val !== null && val !== undefined;
    });

    if (nonEmptyFields.length === 0)
        return <p className="text-gray-500">No history recorded.</p>;

    const handleRemove = (key, isBoolean = false) => {
        if (!setGynaeHistory) return;
        setGynaeHistory({
            ...gynaeHistory,
            [key]: isBoolean ? false : "",
        });
    };

    return (
        <ul className="ml-0 space-y-2 text-sm">
            {nonEmptyFields.map((field, idx) => {
                const value = field.isBoolean ? "Yes" : gynaeHistory[field.key];
                return (
                    <li
                        key={idx}
                        className="relative flex items-center justify-between pl-4"
                    >
                        <span className="absolute top-1 left-0 h-2 w-2 rounded-full bg-gray-600"></span>
                        <span>
                            <strong>{field.label}:</strong> {value}
                        </span>
                        <button
                            type="button"
                            className="ml-2 p-1 text-red-600 hover:text-red-800"
                            onClick={() =>
                                handleRemove(field.key, field.isBoolean)
                            }
                        >
                            <X size={14} />
                        </button>
                    </li>
                );
            })}
        </ul>
    );
}
