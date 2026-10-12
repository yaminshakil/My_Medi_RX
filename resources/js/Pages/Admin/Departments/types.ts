export type HospitalOption = { id: number; hospital_name: string };

export type DoctorAssignment = {
    id: number;
    uuid: string;
    name: string;
    email: string;
    pivot: {
        room_number: string | null;
        extension_number: string | null;
        is_department_head: boolean;
        status: boolean;
    };
};

export type Department = {
    id: number;
    uuid: string;
    hospital_id: number;
    name: string;
    code: string | null;
    description: string | null;
    floor: string | null;
    phone_number: string | null;
    email: string | null;
    sort_order: number;
    status: boolean;
    doctors_count?: number;
    hospital?: HospitalOption;
    doctors?: DoctorAssignment[];
};

export type PaginationLink = { url: string | null; label: string; active: boolean };
export type DepartmentItems = {
    data: Department[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};
