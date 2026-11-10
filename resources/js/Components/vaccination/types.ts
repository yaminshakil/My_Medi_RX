export type VaccineOption = {
    id: number;
    name: string;
};

export type VaccinationSchedule = {
    id: number;
    vaccine_id: number;
    vaccine?: VaccineOption;
    schedule_name: string | null;
    dose_number: number;
    recommended_age_days: number | null;
    interval_days: number | null;
    notes: string | null;
    is_active: boolean;
    created_at?: string;
};

export type VaccinationRecord = {
    id: number;
    vaccine_id: number;
    vaccine?: VaccineOption;
    vaccination_schedule_id: number | null;
    schedule?: VaccinationSchedule | null;
    dose_number: number;
    scheduled_date: string | null;
    administered_at: string | null;
    status: 'scheduled' | 'completed' | 'missed' | 'cancelled';
    provider_name: string | null;
    facility_name: string | null;
    batch_number: string | null;
    notes: string | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
    from: number | null;
    to: number | null;
};
