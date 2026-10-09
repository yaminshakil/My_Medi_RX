import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

export default function DoctorMoreDropdown({ assistant }) {
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

    // Toggle active status
    const toggleActive = () => {
        router.post(route('doctor-assistants.toggleActive', assistant.id));
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
                    <button onClick={toggleActive} className="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                        {assistant.active ? 'Mark as Inactive' : 'Mark as Active'}
                    </button>
                </div>
            )}
        </div>
    );
}
