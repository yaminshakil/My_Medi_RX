
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medication History</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 7px; text-align: left; }
        h2 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h2>Medication History Report</h2>
    <p>Patient: {{ $patient->name }}</p>
    <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Scheduled</th>
                <th>Status</th>
                <th>Taken At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->medication?->medicine_name }}</td>
                    <td>{{ $log->scheduled_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->status }}</td>
                    <td>{{ $log->taken_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
