import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import Box from '@mui/material/Box';
import { DataGrid } from '@mui/x-data-grid';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    TimeScale,
    Title,
    Tooltip,
} from 'chart.js';
import 'chartjs-adapter-date-fns';
import { CalendarDays, Pill, UsersRound } from 'lucide-react';
import { useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement, PointElement, LineElement, TimeScale);

export const loptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Monthly Visited Patients',
        },
    },
};

export const goptions = {
    plugins: {
        title: {
            display: true,
            text: 'Monthly Visited Patients',
        },
    },
    responsive: true,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        x: {
            stacked: true,
        },
        y: {
            stacked: true,
        },
    },
};

export default function EprescriptionDashboard(props) {
    const [open, setOpen] = useState(false);

    const medicinecolumns = [
        { field: 'id', headerName: 'ID', sortable: true, width: 20 },
        { field: 'brand_name', headerName: 'Name', width: 150 },
        { field: 'company_name', headerName: 'Company Name', width: 300 },
        { field: 'total_used', headerName: 'Total Used', width: 100 },
    ];

    const columns = [
        { field: 'id', headerName: 'ID', sortable: true, width: 20 },
        { field: 'company_name', headerName: 'Company Name', width: 450 },
        { field: 'total_used', headerName: 'Total', width: 100 },
    ];

    const handleAddpatient = (e) => {
        e.preventDefault();
        router.visit(route('patients.create'), { method: 'get' });
    };

    const handleCreatePrescription = (e) => {
        e.preventDefault();
        router.get(route('prescriptions.saveandnew'));
    };

    const labels = props?.eprescriptions?.monthlyPrescribes?.original.map((item) => item.month);
    const totalprescribs = props?.eprescriptions?.monthlyPrescribes?.original.map((item) => item.total);

    const ldata = {
        labels,
        datasets: [
            {
                label: 'Total Prescriptions',
                data: totalprescribs,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                tension: 0.1,
            },
        ],
    };

    const data = {
        labels,
        datasets: [
            {
                label: 'Total Prescriptions',
                data: totalprescribs,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
            },
        ],
    };

    const dailydata = {
        labels: props?.eprescriptions?.dailyPrescriptionformattedData?.dailyPrescriptionlabels,
        datasets: [
            {
                label: 'Prescription Last 30 Days',
                data: props?.eprescriptions?.dailyPrescriptionformattedData?.dailyPrescriptiondataset,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
            },
        ],
    };

    const dailyoptions = {
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'day', // Display data by day
                    displayFormats: {
                        day: 'MMM d', // Format for displaying days (e.g., Aug 1)
                    },
                    tooltipFormat: 'PPP', // Format for tooltips (e.g., August 1, 2025)
                },
                title: {
                    display: true,
                    text: 'Date',
                },
            },
            y: {
                title: {
                    display: true,
                    text: 'Daily Prescription',
                },
            },
        },
        responsive: true, // Make the chart responsive to its container
        maintainAspectRatio: false, // Still recommended to set this
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashoboard',
            href: '/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashoboard" />
            <section className="mx-4 py-4">
                <div className="flex flex-col md:flex-row md:justify-end md:space-x-4">
                    <div className="mb-4 md:mb-0">
                        <Button className="w-full text-white md:w-auto" onClick={(e) => handleAddpatient(e)}>
                            + Add Patient
                        </Button>
                    </div>

                    <div className="mb-4 md:mb-0">
                        <Button className="w-full text-white md:w-auto" onClick={(e) => handleCreatePrescription(e)}>
                            + Create Prescription
                        </Button>
                    </div>

                    <div className="relative mb-4">
                        <Button className="w-full text-white md:w-auto" onClick={() => setOpen(!open)}>
                            Switch Chamber
                            <svg className="-mr-1 ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </Button>

                        {open && (
                            <div className="ring-opacity-5 absolute right-0 mt-2 w-full rounded-md bg-white shadow-lg ring-1 ring-black md:w-56">
                                <div className="py-1">
                                    {props.chambers.map((chamber) => (
                                        <Link
                                            key={chamber.id}
                                            href={route('chambers.setDefault', chamber.id)}
                                            className={`${chamber.is_active ? 'bg-[var(--btn-base-color)] text-white' : ''
                                                } block px-4 py-2 text-sm text-gray-700 hover:bg-[var(--btn-base-hover-color)]`}
                                        >
                                            {chamber.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
                <div className="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-6">
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Medicines</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{props?.eprescriptions?.totalmedicine}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Patients</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{props?.eprescriptions?.totalpatients}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <UsersRound className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Total Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{props?.eprescriptions?.totalprescription}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Today's Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.todaytotalprescription}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's New Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.todaynewprescriptions}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Old Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.todayoldprescriptions}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                </div>
                <div className="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Total Appointments</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.appointments?.totalappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Today's Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.appointments?.todaysappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Online Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.appointments?.todayonlineappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Chamber Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {props?.eprescriptions?.appointments?.todayofflineappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-4">
                    <div className="h-[300px] w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Line options={dailyoptions} data={dailydata} />
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Line options={loptions} data={ldata} />
                    </div>
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Bar options={goptions} data={data} />
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <h2 className="mb-1 w-full text-center text-xl font-bold">Top Ten Medicines</h2>
                        <Box sx={{ height: 470, width: '100%' }}>
                            <DataGrid
                                dataSet="Commodity"
                                density="compact"
                                rows={props?.eprescriptions?.toptenmedicines}
                                columns={medicinecolumns}
                                pageSize={10}
                                rowsPerPageOptions={[5]}
                                bulkActionButtons={false}
                            />
                        </Box>
                    </div>
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <h2 className="mb-1 w-full text-center text-xl font-bold">Top Ten Companies</h2>
                        <Box sx={{ height: 470, width: '100%' }}>
                            <DataGrid
                                dataSet="Commodity"
                                density="compact"
                                rows={props?.eprescriptions?.toptencompanies}
                                columns={columns}
                                pageSize={10}
                                rowsPerPageOptions={[5]}
                                bulkActionButtons={false}
                            />
                        </Box>
                    </div>
                </div>
            </section>
        </AppLayout>
    );
}
