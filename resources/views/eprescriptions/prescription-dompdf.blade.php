<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>{{ $prescription->patient->patient_number ?? 'Prescription' }}</title>

    <style>
        /*
         * Dompdf-friendly stylesheet:
         * - No Tailwind, JavaScript, CSS variables, flexbox, grid, transforms or viewport units.
         * - Use tables for predictable column layout.
         * - For Bengali text, configure a locally installed Bengali font in Dompdf.
         */
        @page {
            margin: 28px 32px 65px 32px;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        p {
            margin: 0 0 4px 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid {{ $appSettings['site_base_color'] ?? '#16a34a' }};
            margin-bottom: 12px;
        }

        .header-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 5px 9px 5px;
        }

        .header-left { text-align: left; }
        .header-center { text-align: center; }
        .header-right { text-align: right; }

        .chamber-logo {
            max-width: 100px;
            max-height: 65px;
        }

        .patient-table {
            margin-bottom: 14px;
            border-bottom: 1px solid #cccccc;
        }

        .patient-table td {
            padding: 5px 3px 8px 3px;
            vertical-align: top;
            font-size: 9px;
        }

        .content-table {
            width: 100%;
            table-layout: fixed;
        }

        .sidebar {
            width: 27%;
            vertical-align: top;
            padding: 0 10px 0 0;
            border-right: 1px solid #cccccc;
        }

        .main-content {
            width: 73%;
            vertical-align: top;
            padding: 0 0 0 12px;
        }

        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-title {
            color: {{ $appSettings['site_base_color'] ?? '#16a34a' }};
            font-size: 11px;
            font-weight: bold;
            padding-bottom: 4px;
            margin: 0 0 6px 0;
            border-bottom: 1px solid #cccccc;
        }

        .plain-list {
            margin: 0;
            padding-left: 15px;
        }

        .plain-list li {
            margin-bottom: 3px;
        }

        .medicine-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
        }

        .medicine-table th,
        .medicine-table td {
            border: 1px solid #cccccc;
            padding: 6px 5px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
        }

        .medicine-table th {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .medicine-table tr {
            page-break-inside: avoid;
        }

        .muted {
            color: #666666;
        }

        .signature-area {
            text-align: right;
            margin-top: 25px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .signature-line {
            display: inline-block;
            width: 150px;
            border-top: 1px solid #555555;
            padding-top: 5px;
        }

        .footer {
            position: fixed;
            bottom: -45px;
            left: 0;
            right: 0;
            border-top: 2px solid {{ $appSettings['site_base_color'] ?? '#16a34a' }};
            padding-top: 6px;
            text-align: center;
            font-size: 8px;
            background: #ffffff;
        }

        .powered-by {
            text-align: right;
            font-size: 8px;
            color: {{ $appSettings['site_base_color'] ?? '#16a34a' }};
            margin-top: 5px;
        }

        .watermark {
            position: fixed;
            top: 35%;
            left: 34%;
            font-size: 100px;
            color: #eeeeee;
            font-weight: bold;
            z-index: -1;
        }

        .qr-area {
            text-align: right;
            margin-top: 8px;
        }

        .qr-image {
            width: 75px;
            height: 75px;
        }

        .small {
            font-size: 8px;
        }

        .no-border td {
            border: none;
        }
    </style>
</head>
<body>

    <div class="watermark">Rx</div>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                {!! trim($prescription?->header_left ?? '') !!}
            </td>

            <td class="header-center">
                @if (!empty($prescription->chamber_logo))
                    <img
                        src="{{ public_path('storage/' . $prescription->chamber_logo) }}"
                        alt="Chamber logo"
                        class="chamber-logo"
                    >
                @endif
            </td>

            <td class="header-right">
                {!! trim($prescription?->header_right ?? '') !!}
            </td>
        </tr>
    </table>

    {{-- Patient information --}}
    <table class="patient-table">
        <tr>
            <td>
                Name: <strong>{{ $prescription->patient?->name ?? '-' }}</strong>
            </td>
            <td>
                ID: <strong>{{ $prescription->patient?->patient_number ?? '-' }}</strong>
            </td>
            <td>
                Age:
                <strong>
                    @if ($prescription->patient?->date_of_birth)
                        {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->diff(\Carbon\Carbon::now())->format('%y Yrs, %m Mo') }}
                    @else
                        -
                    @endif
                </strong>
            </td>
            <td>
                Gender: <strong>{{ ucfirst($prescription->patient?->gender ?? '-') }}</strong>
            </td>
            <td>
                Date:
                <strong>{{ $prescription->created_at?->format('d M Y') ?? '-' }}</strong>
            </td>
        </tr>
    </table>

    {{-- Main content: table-based two-column layout --}}
    <table class="content-table">
        <tr>
            {{-- Sidebar --}}
            <td class="sidebar">

                <div class="section">
                    <h3 class="section-title">Chief Complaint</h3>
                    <ul class="plain-list">
                        @forelse(($prescription->symptoms ?? []) as $symptom)
                            <li>{{ data_get($symptom, 'name', '-') }}</li>
                        @empty
                            <li>No Complaint found</li>
                        @endforelse
                    </ul>
                </div>

                @if ($prescription->vitals)
                    <div class="section">
                        <h3 class="section-title">Vital</h3>
                        <ul class="plain-list">
                            @if ($prescription->vitals->blood_pressure)
                                <li>BP: {{ $prescription->vitals->blood_pressure }}</li>
                            @endif
                            @if ($prescription->vitals->heart_rate)
                                <li>HR: {{ $prescription->vitals->heart_rate }}</li>
                            @endif
                            @if ($prescription->vitals->temperature)
                                <li>Temp: {{ $prescription->vitals->temperature }}</li>
                            @endif
                            @if ($prescription->vitals->respiratory_rate)
                                <li>RR: {{ $prescription->vitals->respiratory_rate }}</li>
                            @endif
                            @if ($prescription->vitals->oxygen_saturation)
                                <li>O₂ Sat: {{ $prescription->vitals->oxygen_saturation }}</li>
                            @endif
                            @if ($prescription->vitals->weight)
                                <li>Wt: {{ $prescription->vitals->weight }}</li>
                            @endif
                            @if ($prescription->vitals->height)
                                <li>Ht: {{ $prescription->vitals->height }}</li>
                            @endif
                            @if ($prescription->vitals->bmi)
                                <li>BMI: {{ $prescription->vitals->bmi }}</li>
                            @endif
                            @if ($prescription->vitals->notes)
                                <li>Notes: {{ $prescription->vitals->notes }}</li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if ($prescription->onexaminations)
                    <div class="section">
                        <h3 class="section-title">On Examination (O/Ex)</h3>
                        <ul class="plain-list">
                            @forelse($prescription->onexaminations as $onexamination)
                                <li>{{ data_get($onexamination, 'name', '-') }}</li>
                            @empty
                                <li>No Examination found</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                @if ($prescription->investigations)
                    <div class="section">
                        <h3 class="section-title">Investigation</h3>
                        <ul class="plain-list">
                            @forelse($prescription->investigations as $investigation)
                                <li>{{ data_get($investigation, 'name', '-') }}</li>
                            @empty
                                <li>No Investigation found</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                @if ($prescription->gynaeHistory)
                    <div class="section">
                        <h3 class="section-title">Gyne. &amp; Obs. History</h3>
                        <ul class="plain-list">
                            @php
                                $history = $prescription->gynaeHistory->toArray();
                                $exclude = ['id', 'prescription_id', 'created_at', 'updated_at'];
                            @endphp

                            @foreach ($history as $key => $value)
                                @if (!in_array($key, $exclude, true) && !is_null($value) && $value !== '' && $value !== false)
                                    <li>
                                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                        @if (is_bool($value))
                                            {{ $value ? 'Yes' : 'No' }}
                                        @elseif (is_array($value))
                                            {{ implode(', ', $value) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($prescription->diagnosis)
                    <div class="section">
                        <h3 class="section-title">Diagnosis</h3>
                        <ul class="plain-list">
                            <li>{{ $prescription->diagnosis }}</li>
                        </ul>
                    </div>
                @endif
            </td>

            {{-- Main prescription details --}}
            <td class="main-content">
                <div class="section">
                    <h3 class="section-title">Medicine</h3>

                    <table class="medicine-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Type</th>
                                <th style="width: 29%;">Medicine Name</th>
                                <th style="width: 15%;">Strength</th>
                                <th style="width: 25%;">Doses</th>
                                <th style="width: 19%;">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prescription->medications as $med)
                                <tr>
                                    <td>{{ $med->type ?? '-' }}</td>
                                    <td>{{ $med->medicines->brand_name ?? '-' }}</td>
                                    <td>{{ $med->strength ?? '-' }}</td>
                                    <td>
                                        {{ $med->dosage ?? '-' }}
                                        @if ($med->meal_time)
                                            <br>{{ $med->meal_time }}
                                        @endif
                                    </td>
                                    <td>{{ $med->duration ?? '-' }}</td>
                                </tr>

                                @if ($med->advice)
                                    <tr>
                                        <td colspan="5">
                                            <strong>Advice:</strong> {{ $med->advice }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5">No medicines prescribed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($prescription->instructions)
                    <div class="section">
                        <h3 class="section-title">Advice / Instructions</h3>
                        <ul class="plain-list">
                            @foreach (preg_split('/\r\n|\r|\n/', $prescription->instructions) as $line)
                                @if (trim($line) !== '')
                                    <li>{{ $line }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($prescription->follow_up_advice)
                    <div class="section">
                        <h3 class="section-title">Follow-up Advice</h3>
                        <p>{{ $prescription->follow_up_advice }}</p>
                    </div>
                @endif

                @if ($prescription->followup_date)
                    <div class="section">
                        <h3 class="section-title">Follow-up Date</h3>
                        <p>{{ $prescription->followup_date->format('d M Y') }}</p>
                    </div>
                @endif

                @if (!empty($qrCodeImage))
                    <div class="qr-area">
                        <img src="{{ $qrCodeImage }}" alt="Prescription verification QR" class="qr-image">
                        <p class="small">Scan to verify this prescription</p>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Signature --}}
    <div class="signature-area">
        <div class="signature-line">
            <strong>{{ $prescription->doctor?->name ?? '-' }}</strong><br>
            <span class="small">Doctor's signature</span>
        </div>
    </div>

    {{-- Fixed footer --}}
    <div class="footer">
        {!! trim($prescription->footer_info ?? '') !!}
        <div class="powered-by">
            Powered by {{ $appSettings['site_title'] ?? config('app.name') }}
        </div>
    </div>

</body>
</html>
