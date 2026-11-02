<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Clinical Analytics</title>
    <style>
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, h2 { color: #123b62; }
        h1 { margin-bottom: 4px; }
        .muted { color: #64748b; }
        .metrics { border-collapse: collapse; margin: 12px 0 22px; width: 100%; }
        .metrics th, .metrics td { border: 1px solid #dbe3ec; padding: 7px; text-align: left; }
        .metrics th { background: #eef4f8; }
        .section { margin-top: 24px; }
    </style>
</head>
<body>
    <h1>Doctor Clinical Analytics</h1>
    <p>{{ $doctor->name }} · {{ $profile?->specialization ?? 'Specialization not recorded' }}</p>
    <p class="muted">Generated {{ now()->format('d M Y H:i') }}</p>

    <h2 class="section">Prescription statistics</h2>
    <table class="metrics">
        <thead>
            <tr>
                <th>Period</th>
                <th>Patients</th>
                <th>Prescriptions issued</th>
                <th>New visits</th>
                <th>Follow-up visits</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Today</td>
                <td>{{ $dashboard['daily']['patients'] }}</td>
                <td>{{ $dashboard['daily']['prescriptions'] }}</td>
                <td>{{ $dashboard['daily']['new_visits'] }}</td>
                <td>{{ $dashboard['daily']['follow_up_visits'] }}</td>
            </tr>
            <tr>
                <td>This month</td>
                <td>{{ $dashboard['monthly']['patients'] }}</td>
                <td>{{ $dashboard['monthly']['prescriptions'] }}</td>
                <td>{{ $dashboard['monthly']['new_visits'] }}</td>
                <td>{{ $dashboard['monthly']['follow_up_visits'] }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Patient adherence (last {{ $dashboard['adherence']['period_days'] }} days)</h2>
    <table class="metrics">
        <tbody>
            <tr><th>Compliance rate</th><td>{{ $dashboard['adherence']['percentage'] === null ? 'No eligible doses' : $dashboard['adherence']['percentage'].'%' }}</td></tr>
            <tr><th>Eligible doses</th><td>{{ $dashboard['adherence']['eligible_doses'] }}</td></tr>
            <tr><th>Taken</th><td>{{ $dashboard['adherence']['taken'] }}</td></tr>
            <tr><th>Missed</th><td>{{ $dashboard['adherence']['missed'] }}</td></tr>
            <tr><th>Skipped (excluded from rate)</th><td>{{ $dashboard['adherence']['skipped'] }}</td></tr>
        </tbody>
    </table>

    @if (count($dashboard['patient_adherence']) > 0)
        <h2>Patient compliance overview</h2>
        <table class="metrics">
            <thead>
                <tr><th>Patient</th><th>Patient number</th><th>Compliance rate</th><th>Taken</th><th>Missed</th></tr>
            </thead>
            <tbody>
                @foreach ($dashboard['patient_adherence'] as $patient)
                    <tr>
                        <td>{{ $patient['patient_name'] }}</td>
                        <td>{{ $patient['patient_number'] }}</td>
                        <td>{{ $patient['percentage'] }}%</td>
                        <td>{{ $patient['taken'] }}</td>
                        <td>{{ $patient['missed'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 class="section">Prescription log (latest 100)</h2>
    <table class="metrics">
        <thead>
            <tr><th>Date</th><th>Patient</th><th>Patient number</th><th>Prescription</th><th>Diagnosis</th><th>Medicines</th><th>Visit type</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($prescriptions as $prescription)
                <tr>
                    <td>{{ $prescription->created_at?->format('d M Y') }}</td>
                    <td>{{ $prescription->patient?->name ?? 'Patient unavailable' }}</td>
                    <td>{{ $prescription->patient?->patient_number ?? '—' }}</td>
                    <td>{{ $prescription->prescription_number }}</td>
                    <td>{{ $prescription->diagnosis ?? '—' }}</td>
                    <td>
                        @foreach ($prescription->medications as $medication)
                            {{ $medication->medicine?->brand_name ?? $medication->medicine?->generic_name ?? 'Medicine' }}
                            {{ $medication->strength }} {{ $medication->dosage }}@if (! $loop->last); @endif
                        @endforeach
                    </td>
                    <td>{{ $prescription->is_followup ? 'Follow-up' : 'New' }}</td>
                    <td>{{ ucfirst($prescription->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No prescriptions found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
