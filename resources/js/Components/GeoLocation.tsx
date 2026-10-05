import React, { useState, useEffect } from "react";
import axios from 'axios';
import Select from 'react-select';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

export default function GeoLocation({data, setData, errors}) {
    const [divisions, setDivisions] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [thanas, setThanas] = useState([]);

    const [selectedDivision, setSelectedDivision] = useState(null);
    const [selectedDistrict, setSelectedDistrict] = useState(null);
    const [selectedThana, setSelectedThana] = useState(null);

    // Load divisions
    useEffect(() => {
        axios.get('/divisions').then(res => {
            const options = res.data.map(d => ({ value: d.id, label: d.name }));
            setDivisions(options);

            // Preselect division for edit
            if (data.division_id) {
                const divOption = options.find(o => o.value === data.division_id);
                setSelectedDivision(divOption);
            }
        });
    }, []);

    // Load districts if division is set
    useEffect(() => {
        if (selectedDivision) {
            setData('division_id', selectedDivision.value);
            axios.get(`/districts/${selectedDivision.value}`).then(res => {
                const options = res.data.map(d => ({ value: d.id, label: d.name }));
                setDistricts(options);

                // Preselect district for edit
                if (data.district_id) {
                    const distOption = options.find(o => o.value === data.district_id);
                    setSelectedDistrict(distOption);
                }
            });
        }
    }, [selectedDivision]);

    // Load thanas if district is set
    useEffect(() => {
        if (selectedDistrict) {
            setData('district_id', selectedDistrict.value);
            axios.get(`/thanas/${selectedDistrict.value}`).then(res => {
                const options = res.data.map(t => ({ value: t.id, label: t.name }));
                setThanas(options);

                // Preselect thana for edit
                if (data.thana_id) {
                    const thanaOption = options.find(o => o.value === data.thana_id);
                    setSelectedThana(thanaOption);
                }
            });
        }
    }, [selectedDistrict]);

    // Update thana_id when thana changes
    useEffect(() => {
        if (selectedThana) {
            setData('thana_id', selectedThana.value);
        }
    }, [selectedThana]);
    return (
        <>
            <div className="mt-4">
                <InputLabel htmlFor="division" value="Division" />

                <Select
                    placeholder="Select Division"
                    options={divisions}
                    value={selectedDivision}
                    onChange={setSelectedDivision}
                />

                {errors.division_id && <InputError className="mt-2 text-red-700" message={errors.division_id} />}
            </div>

            <div className="mt-4">
                <InputLabel htmlFor="district" value="District" />

                {/* District */}
                <Select
                    placeholder="Select District"
                    options={districts}
                    value={selectedDistrict}
                    onChange={setSelectedDistrict}
                    isDisabled={!selectedDivision}
                />

                {errors.district_id && <InputError className="mt-2 text-red-700" message={errors.district_id} />}
            </div>

            <div className="mt-4">
                <InputLabel htmlFor="thana" value="PS.(Thana)" />

                {/* Thana */}
                <Select
                    placeholder="Select Thana"
                    options={thanas}
                    value={selectedThana}
                    onChange={setSelectedThana}
                    isDisabled={!selectedDistrict}
                />

                {errors.thana_id && <InputError className="mt-2 text-red-700" message={errors.thana_id} />}
            </div>
        </>
    )
}