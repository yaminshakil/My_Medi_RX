import DoctorFilterChart from '@/Components/DoctorFilterChart';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
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

export default function Dashboard() {
    const { eprescriptions } = usePage().props;

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

    const labels = eprescriptions?.monthlyPrescribes?.original.map((item) => item.month);
    const totalprescribs = eprescriptions?.monthlyPrescribes?.original.map((item) => item.total);

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
        labels: eprescriptions?.dailyPrescriptionformattedData?.dailyPrescriptionlabels,
        datasets: [
            {
                label: 'Prescription Last 30 Days',
                data: eprescriptions?.dailyPrescriptionformattedData?.dailyPrescriptiondataset,
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
                <div className="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Patients</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{eprescriptions?.totalpatients}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <UsersRound className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Total Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{eprescriptions?.totalprescription}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Today's Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{eprescriptions?.todaytotalprescription}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's New Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{eprescriptions?.todaynewprescriptions}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Old Prescriptions</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{eprescriptions?.todayoldprescriptions}</div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <Pill className="text-xl font-medium text-white" />
                        </div>
                    </div>
                </div>
                <div className="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Total Appointments</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {eprescriptions?.appointments?.totalappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center truncate text-sm font-medium text-white">Today's Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {eprescriptions?.appointments?.todaysappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Online Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {eprescriptions?.appointments?.todayonlineappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                    <div className="w-full rounded-lg bg-[var(--base-color)] px-4 py-5 shadow">
                        <div className="flex justify-center text-center text-sm font-medium text-white">Today's Chamber Appointemtns</div>
                        <div className="mt-1 flex justify-center text-3xl font-semibold text-white">
                            {eprescriptions?.appointments?.todayofflineappointments}
                        </div>
                        <div className="mt-2 flex justify-center truncate text-sm font-medium text-gray-500">
                            <CalendarDays className="text-xl font-medium text-white" />
                        </div>
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-6">
                    <div className="h-[330px] w-full rounded-lg bg-white px-4 py-5 shadow">
                        <DoctorFilterChart data={eprescriptions.dailychartData} doctorsMap={eprescriptions.doctorsMap} />
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-6">
                    <div className="h-[300px] w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Line options={dailyoptions} data={dailydata} />
                    </div>
                </div>

                <div className="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Line options={loptions} data={ldata} />
                    </div>
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <Bar options={goptions} data={data} />
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div className="w-full rounded-lg bg-white px-4 py-5 shadow">
                        <h2 className="mb-1 w-full text-center text-xl font-bold">Top Ten Medicines</h2>
                        <Box sx={{ height: 470, width: '100%' }}>
                            <DataGrid
                                dataSet="Commodity"
                                density="compact"
                                rows={eprescriptions?.toptenmedicines}
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
                                rows={eprescriptions?.toptencompanies}
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
