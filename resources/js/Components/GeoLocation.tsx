import React, { useEffect, useState } from 'react';
import axios from 'axios';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

import { Label } from '@/Components/ui/label';

interface LocationItem {
    id: number;
    name: string;
}

interface GeoLocationProps {
    data: {
        division_id?: number | string | null;
        district_id?: number | string | null;
        thana_id?: number | string | null;
    };
    setData: (key: string, value: number | string | null) => void;
    errors: {
        division_id?: string;
        district_id?: string;
        thana_id?: string;
    };
}

export default function GeoLocation({
    data,
    setData,
    errors,
}: GeoLocationProps) {
    const [divisions, setDivisions] = useState<LocationItem[]>([]);
    const [districts, setDistricts] = useState<LocationItem[]>([]);
    const [thanas, setThanas] = useState<LocationItem[]>([]);

    const [loadingDivisions, setLoadingDivisions] = useState(false);
    const [loadingDistricts, setLoadingDistricts] = useState(false);
    const [loadingThanas, setLoadingThanas] = useState(false);

    /**
     * Load divisions
     */
    useEffect(() => {
        const loadDivisions = async () => {
            try {
                setLoadingDivisions(true);

                const response = await axios.get('/divisions');

                setDivisions(response.data);
            } catch (error) {
                console.error('Failed to load divisions:', error);
            } finally {
                setLoadingDivisions(false);
            }
        };

        loadDivisions();
    }, []);

    /**
     * Load districts when division changes
     */
    useEffect(() => {
        if (!data.division_id) {
            setDistricts([]);
            setThanas([]);
            return;
        }

        const loadDistricts = async () => {
            try {
                setLoadingDistricts(true);

                const response = await axios.get(
                    `/districts/${data.division_id}`
                );

                setDistricts(response.data);
            } catch (error) {
                console.error('Failed to load districts:', error);
                setDistricts([]);
            } finally {
                setLoadingDistricts(false);
            }
        };

        loadDistricts();
    }, [data.division_id]);

    /**
     * Load thanas when district changes
     */
    useEffect(() => {
        if (!data.district_id) {
            setThanas([]);
            return;
        }

        const loadThanas = async () => {
            try {
                setLoadingThanas(true);

                const response = await axios.get(
                    `/thanas/${data.district_id}`
                );

                setThanas(response.data);
            } catch (error) {
                console.error('Failed to load thanas:', error);
                setThanas([]);
            } finally {
                setLoadingThanas(false);
            }
        };

        loadThanas();
    }, [data.district_id]);

    /**
     * Division change
     */
    const handleDivisionChange = (value: string) => {
        const divisionId = Number(value);

        setData('division_id', divisionId);

        // Reset dependent fields
        setData('district_id', null);
        setData('thana_id', null);

        setDistricts([]);
        setThanas([]);
    };

    /**
     * District change
     */
    const handleDistrictChange = (value: string) => {
        const districtId = Number(value);

        setData('district_id', districtId);

        // Reset dependent field
        setData('thana_id', null);

        setThanas([]);
    };

    /**
     * Thana change
     */
    const handleThanaChange = (value: string) => {
        setData('thana_id', Number(value));
    };

    return (
        <div className="space-y-5">
            {/* Division */}
            <div className="space-y-2">
                <Label htmlFor="division">Division</Label>

                <Select
                    value={data.division_id?.toString() ?? ''}
                    onValueChange={handleDivisionChange}
                >
                    <SelectTrigger id="division" className="w-full">
                        <SelectValue
                            placeholder={
                                loadingDivisions
                                    ? 'Loading divisions...'
                                    : 'Select Division'
                            }
                        />
                    </SelectTrigger>

                    <SelectContent>
                        {divisions.map((division) => (
                            <SelectItem
                                key={division.id}
                                value={division.id.toString()}
                            >
                                {division.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                {errors.division_id && (
                    <p className="text-sm font-medium text-destructive">
                        {errors.division_id}
                    </p>
                )}
            </div>

            {/* District */}
            <div className="space-y-2">
                <Label htmlFor="district">District</Label>

                <Select
                    value={data.district_id?.toString() ?? ''}
                    onValueChange={handleDistrictChange}
                    disabled={!data.division_id || loadingDistricts}
                >
                    <SelectTrigger id="district" className="w-full">
                        <SelectValue
                            placeholder={
                                loadingDistricts
                                    ? 'Loading districts...'
                                    : 'Select District'
                            }
                        />
                    </SelectTrigger>

                    <SelectContent>
                        {districts.map((district) => (
                            <SelectItem
                                key={district.id}
                                value={district.id.toString()}
                            >
                                {district.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                {errors.district_id && (
                    <p className="text-sm font-medium text-destructive">
                        {errors.district_id}
                    </p>
                )}
            </div>

            {/* Thana */}
            <div className="space-y-2">
                <Label htmlFor="thana">PS. (Thana)</Label>

                <Select
                    value={data.thana_id?.toString() ?? ''}
                    onValueChange={handleThanaChange}
                    disabled={!data.district_id || loadingThanas}
                >
                    <SelectTrigger id="thana" className="w-full">
                        <SelectValue
                            placeholder={
                                loadingThanas
                                    ? 'Loading thanas...'
                                    : 'Select Thana'
                            }
                        />
                    </SelectTrigger>

                    <SelectContent>
                        {thanas.map((thana) => (
                            <SelectItem
                                key={thana.id}
                                value={thana.id.toString()}
                            >
                                {thana.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                {errors.thana_id && (
                    <p className="text-sm font-medium text-destructive">
                        {errors.thana_id}
                    </p>
                )}
            </div>
        </div>
    );
}
