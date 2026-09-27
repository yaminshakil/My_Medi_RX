import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import React, { useEffect, useState } from 'react';

export default function AddVital({ vitalData, setVitalData, onHandleChange }) {
    const [category, setCategory] = useState('');
    useEffect(() => {
        if (!vitalData.weight || !vitalData.height) return;

        // Split into feet and inches
        const feet = Math.floor(vitalData.height); // whole number = feet
        const decimalPart = vitalData.height - feet; // decimal part = inches in fraction
        const inches = decimalPart * 12;

        const totalInches = feet * 12 + inches;
        const heightInMeters = totalInches * 0.0254;
        const bmiValue = (vitalData.weight / (heightInMeters * heightInMeters)).toFixed(2);
        setVitalData('bmi', bmiValue);
        // Set category
        if (bmiValue < 18.5) {
            setCategory('Underweight');
        } else if (bmiValue >= 18.5 && bmiValue < 24.9) {
            setCategory('Normal weight');
        } else if (bmiValue >= 25 && bmiValue < 29.9) {
            setCategory('Overweight');
        } else {
            setCategory('Obese');
        }
    }, [vitalData.weight, vitalData.height]);
    return (
        <React.Fragment>
            <form className="order mb-4 border-solid border-gray-300 p-3">
                <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div className="min-w-0">
                        <Label htmlFor="blood_pressure" className="block">
                            Blood Pressure
                        </Label>
                        <Input
                            id="blood_pressure"
                            name="blood_pressure"
                            value={vitalData.blood_pressure}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                        />
                    </div>
                    <div className="min-w-0">
                        <Label htmlFor="heart_rate" className="block">
                            Heart Rate
                        </Label>
                        <Input
                            id="heart_rate"
                            name="heart_rate"
                            type="number"
                            value={vitalData.heart_rate}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                        />
                    </div>
                </div>
                <div className="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div className="min-w-0">
                        <Label htmlFor="temperature" className="block">
                            Temperature
                        </Label>
                        <Input
                            id="temperature"
                            name="temperature"
                            type="number"
                            step="0.1"
                            value={vitalData.temperature}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                        />
                    </div>
                    <div className="min-w-0">
                        <Label htmlFor="respiratory_rate" className="block">
                            Respiratory Rate
                        </Label>
                        <Input
                            id="respiratory_rate"
                            name="respiratory_rate"
                            type="number"
                            step="0.1"
                            value={vitalData.respiratory_rate}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                        />
                    </div>
                </div>
                <div className="mt-4">
                    <Label htmlFor="oxygen_saturation" className="block">
                        Oxygen Saturation
                    </Label>
                    <Input
                        id="oxygen_saturation"
                        name="oxygen_saturation"
                        type="number"
                        step="0.1"
                        value={vitalData.oxygen_saturation}
                        onChange={onHandleChange}
                        className="w-full border p-2"
                    />
                </div>
                <div className="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div className="min-w-0">
                        <Label htmlFor="weight" className="block">
                            Weight
                        </Label>
                        <Input id="weight" name="weight" value={vitalData.weight} onChange={onHandleChange} className="w-full border p-2" />
                    </div>
                    <div className="min-w-0">
                        <Label htmlFor="height" className="block">
                            Height
                        </Label>
                        <Input
                            id="height"
                            name="height"
                            type="number"
                            value={vitalData.height}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                        />
                    </div>
                </div>
                <div className="mt-4">
                    <Label htmlFor="bmi" className="block">
                        BMI
                    </Label>
                    <Input
                        id="bmi"
                        name="bmi"
                        type="number"
                        step="0.1"
                        value={vitalData.bmi}
                        onChange={onHandleChange}
                        className="w-full border p-2"
                    />
                    <p className="text-md text-gray-700">Category: {category}</p>
                </div>
                <div className="mt-4">
                    <Label htmlFor="notes" className="block">
                        Notes
                    </Label>
                    <Textarea
                        id="notes"
                        name="notes"
                        value={vitalData.notes}
                        onChange={onHandleChange}
                        className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
            </form>
        </React.Fragment>
    );
}
