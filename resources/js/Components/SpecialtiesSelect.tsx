import { Select } from "antd";

const { Option, OptGroup } = Select;

interface OptionItem {
    value: number | string;
    label: string;
}

interface GroupItem {
    label: string;
    options: OptionItem[];
}

interface Props {
    groups: GroupItem[];
    value: (number | string)[];
    onChange: (val: (number | string)[]) => void;
}

export default function SpecialtiesSelect({ groups, value, onChange }: Props) {
    return (
        <Select
            mode="multiple"
            value={value}
            onChange={onChange}
            placeholder="Select Specialties"
            style={{ width: "100%" }}
            optionLabelProp="label"
            showSearch
            tagRender={(props) => {
                const { label, closable, onClose } = props;
                return (
                    <span
                        style={{
                            backgroundColor: "var(--base-color)",
                            color: "#fff",
                            padding: "2px 6px",
                            borderRadius: 4,
                            marginRight: 4,
                            display: "inline-flex",
                            alignItems: "center",
                        }}
                    >
                        {label}
                        {closable && (
                            <span
                                onClick={onClose}
                                style={{ marginLeft: 4, cursor: "pointer" }}
                            >
                                ×
                            </span>
                        )}
                    </span>
                );
            }}
        >
            {groups.map((group) => (
                <OptGroup key={group.label} label={group.label}>
                    {group.options.map((option) => (
                        <Option
                            key={option.value}
                            value={option.value}
                            label={option.label}
                        >
                            {option.label}
                        </Option>
                    ))}
                </OptGroup>
            ))}
        </Select>
    );
}
