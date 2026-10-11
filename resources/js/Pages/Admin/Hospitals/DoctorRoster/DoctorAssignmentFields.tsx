import React, { useEffect, useState } from "react";
import axios from "axios";

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Label } from "@/Components/ui/label";

type Option = {
    id: number;
    name: string;
};

type Hospital = {
    id: number;
    hospital_name: string;
};

type Shift = {
    id: number;
    hospital_id: number;
    name: string;
    start_time: string;
    end_time: string;
    is_active: boolean;
};

type Props = {
    hospitals: Hospital[];
    shifts: Shift[];
    hospitalId: string;
    departmentId: string;
    doctorId: string;
    shiftId: string;
    errors: Partial<
        Record<
            "hospital_id" | "department_id" | "doctor_id" | "shift_id",
            string
        >
    >;
    setHospitalId: (value: string) => void;
    setDepartmentId: (value: string) => void;
    setDoctorId: (value: string) => void;
    setShiftId: (value: string) => void;
};

function getItems<T>(response: unknown): T[] {
    const result = response as {
        data?: T[] | { data?: T[] };
    };

    if (Array.isArray(result.data)) return result.data;
    if (Array.isArray(result.data?.data)) return result.data.data;

    return [];
}

export default function DoctorAssignmentFields({
    hospitals,
    shifts,
    hospitalId,
    departmentId,
    doctorId,
    shiftId,
    errors,
    setHospitalId,
    setDepartmentId,
    setDoctorId,
    setShiftId,
}: Props) {
    const [departments, setDepartments] = useState<Option[]>([]);
    const [doctors, setDoctors] = useState<Option[]>([]);

    const [loadingDepartments, setLoadingDepartments] = useState(false);
    const [loadingDoctors, setLoadingDoctors] = useState(false);

    const [departmentError, setDepartmentError] = useState("");
    const [doctorError, setDoctorError] = useState("");
    const availableShifts = shifts.filter(
        (shift) => shift.hospital_id === Number(hospitalId) && shift.is_active,
    );

    // Load departments whenever the hospital changes.
    useEffect(() => {
        let active = true;

        setDepartments([]);
        setDoctors([]);
        setDepartmentError("");
        setDoctorError("");

        if (!hospitalId) {
            setLoadingDepartments(false);
            setLoadingDoctors(false);
            return;
        }

        setLoadingDepartments(true);

        axios
            .get(`/admin/api/hospitals/${hospitalId}/departments`)
            .then((response) => {
                if (active) {
                    setDepartments(getItems<Option>(response.data));
                }
            })
            .catch(() => {
                if (active) {
                    setDepartmentError("Unable to load departments.");
                }
            })
            .finally(() => {
                if (active) setLoadingDepartments(false);
            });

        return () => {
            active = false;
        };
    }, [hospitalId]);

    // Load only doctors assigned to the selected department.
    useEffect(() => {
        let active = true;

        setDoctors([]);
        setDoctorError("");

        if (!hospitalId || !departmentId) {
            setLoadingDoctors(false);
            return;
        }

        setLoadingDoctors(true);

        axios
            .get(`/admin/api/departments/${departmentId}/doctors`, {
                params: { hospital_id: hospitalId },
            })
            .then((response) => {
                if (active) {
                    setDoctors(getItems<Option>(response.data));
                }
            })
            .catch(() => {
                if (active) {
                    setDoctorError("Unable to load department doctors.");
                }
            })
            .finally(() => {
                if (active) setLoadingDoctors(false);
            });

        return () => {
            active = false;
        };
    }, [hospitalId, departmentId]);

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
            {/* Hospital */}
            <div className="space-y-2">
                <Label>Hospital *</Label>

                <Select
                    value={hospitalId}
                    onValueChange={(value) => {
                        setHospitalId(value);
                        setDepartmentId("");
                        setDoctorId("");
                        setShiftId("");
                    }}
                >
                    <SelectTrigger>
                        <SelectValue placeholder="Select hospital" />
                    </SelectTrigger>

                    <SelectContent>
                        {hospitals.map((hospital) => (
                            <SelectItem
                                key={hospital.id}
                                value={String(hospital.id)}
                            >
                                {hospital.hospital_name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.hospital_id && (
                    <p className="text-sm text-destructive">
                        {errors.hospital_id}
                    </p>
                )}
            </div>

            {/* Department */}
            {hospitalId && (
                <div className="space-y-2">
                    <Label>Department *</Label>

                    <Select
                        value={departmentId}
                        onValueChange={(value) => {
                            setDepartmentId(value);
                            setDoctorId("");
                            setShiftId("");
                        }}
                        disabled={!hospitalId || loadingDepartments}
                    >
                        <SelectTrigger>
                            <SelectValue
                                placeholder={
                                    !hospitalId
                                        ? "Select hospital first"
                                        : loadingDepartments
                                          ? "Loading departments..."
                                          : "Select department"
                                }
                            />
                        </SelectTrigger>

                        <SelectContent>
                            {departments.map((department) => (
                                <SelectItem
                                    key={department.id}
                                    value={String(department.id)}
                                >
                                    {department.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {errors.department_id && (
                        <p className="text-sm text-destructive">
                            {errors.department_id}
                        </p>
                    )}

                    {departmentError && (
                        <p className="text-sm text-destructive">
                            {departmentError}
                        </p>
                    )}

                    {hospitalId &&
                        !loadingDepartments &&
                        !departmentError &&
                        departments.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                No departments found for this hospital.
                            </p>
                        )}
                </div>
            )}

            {/* Doctor */}
            {departmentId && (
                <div className="space-y-2">
                    <Label>Doctor *</Label>

                    <Select
                        value={doctorId}
                        onValueChange={setDoctorId}
                        disabled={
                            !departmentId ||
                            loadingDoctors ||
                            departments.length === 0
                        }
                    >
                        <SelectTrigger>
                            <SelectValue
                                placeholder={
                                    !departmentId
                                        ? "Select department first"
                                        : loadingDoctors
                                          ? "Loading doctors..."
                                          : "Select doctor"
                                }
                            />
                        </SelectTrigger>

                        <SelectContent>
                            {doctors.map((doctor) => (
                                <SelectItem
                                    key={doctor.id}
                                    value={String(doctor.id)}
                                >
                                    {doctor.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {errors.doctor_id && (
                        <p className="text-sm text-destructive">
                            {errors.doctor_id}
                        </p>
                    )}

                    {doctorError && (
                        <p className="text-sm text-destructive">
                            {doctorError}
                        </p>
                    )}

                    {departmentId &&
                        !loadingDoctors &&
                        !doctorError &&
                        doctors.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                No doctors assigned to this department.
                            </p>
                        )}
                </div>
            )}

            {/* Shift */}
            {doctorId && (
                <div className="space-y-2">
                    <Label>Shift (optional)</Label>
                    <Select
                        value={shiftId || "none"}
                        onValueChange={(value) =>
                            setShiftId(value === "none" ? "" : value)
                        }
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select shift" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">No shift</SelectItem>
                            {availableShifts.map((shift) => (
                                <SelectItem
                                    key={shift.id}
                                    value={String(shift.id)}
                                >
                                    {shift.name} ({shift.start_time}–
                                    {shift.end_time})
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {errors.shift_id && (
                        <p className="text-sm text-destructive">
                            {errors.shift_id}
                        </p>
                    )}
                    {availableShifts.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No active shifts are set up for this hospital.
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
