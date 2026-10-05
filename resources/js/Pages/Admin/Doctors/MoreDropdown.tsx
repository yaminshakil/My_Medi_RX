import { Button } from '@/Components/ui/button';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

export default function DoctorMoreDropdown({ doctor }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    // Close dropdown if clicked outside
    useEffect(() => {
        function handleClickOutside(e) {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Toggle featured status
    const toggleFeatured = () => {
        router.post(route('doctors.toggleFeatured', doctor.uuid));
        setOpen(false);
    };

    // Toggle active status
    const toggleActive = () => {
        router.post(route('doctors.toggleActive', doctor.uuid));
        setOpen(false);
    };

    return (
        <div className="relative inline-block" ref={dropdownRef}>
            {/* Button with vertical ... and More */}
            <Button onClick={() => setOpen(!open)} className="ml-2 cursor-pointer">
                <span className="text-xl leading-none">⋮</span>
                <span className="text-xs">More</span>
            </Button>

            {/* Dropdown */}
            {open && (
                <div className="absolute right-0 z-50 mt-2 w-48 rounded-lg border bg-white shadow-lg">
                    <button onClick={toggleFeatured} className="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                        {doctor?.featured ? 'Mark as Non Featured' : 'Mark as Featured'}
                    </button>

                    <button onClick={toggleActive} className="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                        {doctor?.active ? 'Mark as Inactive' : 'Mark as Active'}
                    </button>
                </div>
            )}
        </div>
    );
}
