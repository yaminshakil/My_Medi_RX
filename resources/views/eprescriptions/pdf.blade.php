<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>{{ $prescription->patient->patient_number }}</title>
    <link rel="icon" href="{{ ($appSettings['favicon'] ?? '') ? asset('storage/' . $appSettings['favicon']) : asset('default/favicon.ico')  }}" type="image/x-icon"/>
    <script src="{{ asset('assets/js/tailwindcss.js') }}"></script>
    <script>
        // Auto open browser print dialog on page load
        window.onload = function() {
            window.print();
        };
    </script>
    @php
        function hexToRgb($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) === 3) {
                $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            return "$r, $g, $b";
        }

        $themeColor = $appSettings['site_base_color'] ?? '#16a34a';  // fallback if not set
        $themeRgb = hexToRgb($themeColor);
    @endphp

    <style>
        :root {
            --base-color-rgb: {{ $themeRgb }};
            --base-color: {{ $appSettings['site_base_color'] ?? '#16a34a' }};
        }
    </style>
    <style>
     @media print {
        @page {
            margin: 40px 40px 0px 40px;
        }
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            margin:0;
        }
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600&display=swap');
        .watermark {
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 120px;
            color: rgba(var(--base-color-rgb), 0.1); /* Tailwind green-500 opacity */
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 0;
            padding: 0;
            font-weight: normal; /* Removes default bolding */
            font-size: 1em; /* Resets font size to the inherited size */
            }
        header h1 span { font-size: 2em; }
        header h2 span { font-size: 1.25rem !important; font-weight: 500 !important;}
        header h3 span { font-size: 1.17em; }
         p { margin: 0 0 0.2em 0; font-size: 12px; }
         ul { list-style: disc; padding-left: 1.5em; }
         ol { list-style: decimal; padding-left: 1.5em; }
         blockquote { border-left: 4px solid #ccc; margin: 0; padding-left: 1em; color: #666; }
         a { color: #06c; text-decoration: underline; }
         ul.instructions { page-break-inside: avoid; }

         .footer-area {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
         }

        .signature {
            text-align: right;
            margin-bottom: 10px;
        }

        .print-footer {
            border-top: 2px solid var(--base-color);
            background: white;
            text-align: center;
            font-size: 10px;
            padding: 4px 0;
            width: 100vw; /* ensures full viewport width in print */
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            box-sizing: border-box;
        }

        .border-base {
            border-color: var(--base-color) !important;
        }
        .text-base-color {
            color: var(--base-color);
        }

        }
    </style>
</head>
<body class="bg-white text-gray-800 text-[12px] min-h-screen flex flex-col relative">

    <!-- Watermark -->
    <div class="watermark">℞</div>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col">

        <!-- Header -->
        <header class="flex justify-between items-start gap-3 border-b-2 border-base pb-2 mb-4">
            <div class="w-1/3">
                
                    {!! trim($prescription?->header_left) !!}
                
            </div>
            @if ($prescription->chamber_logo)
            <div class="flex w-1/3 justify-center">
                 
                    <img src="{{ asset('storage/'.$prescription->chamber_logo) }}" alt="Chamber logo" class="h-20">
                
                
            </div>
            @endif
            <div class="text-right w-1/3">
                
                    {!! trim($prescription?->header_right) !!}
                
            </div>
        </header>

        <!-- Patient Info -->
        <section class="flex justify-between items-center text-xs mb-4">
            <div>Name: <strong>{{ $prescription->patient?->name ?? '-' }}</strong></div>
            <div>ID: <strong>{{ $prescription->patient?->patient_number ?? '-' }}</strong></div>
            <div>Years: <strong>{{ \Carbon\Carbon::parse($prescription->patient?->date_of_birth)->diff(\Carbon\Carbon::now())->format('%y Yrs, %m Mo') }}</strong> </div>
            <div>Gender: <strong>{{ ucfirst($prescription->patient?->gender) ?? '-' }}</strong></div>
            <div>Date: <strong>{{ $prescription?->created_at->format('d M Y') ?? '-' }}</strong></div>
        </section>

        <!-- Content with Sidebar -->
         <div class="flex-1 flex flex-col pb-10">
            <div class="grid grid-cols-4 gap-4 flex-1">
            <!-- Sidebar -->
            <aside class="col-span-1 border-r pr-3">
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">Chief Complaint</h2>
                    <ul class="list-disc list-inside space-y-1">
                        @forelse($prescription->symptoms as $symptom)
                            <li>{{ $symptom['name'] ?? '-' }}</li>
                        @empty
                            <li>No Complaint found</li>
                        @endforelse
                    </ul>
                </div>
                @if ($prescription->vitals)
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">Vital</h2>
                    <ul class="list-disc list-inside space-y-1">
                        
                        @if ($prescription->vitals->blood_pressure)
                        <li>BP: {{ $prescription->vitals->blood_pressure ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->heart_rate)
                        <li>HR: {{ $prescription->vitals->heart_rate ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->temperature)
                        <li>Temp: {{ $prescription->vitals->temperature ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->respiratory_rate)
                        <li>RR: {{ $prescription->vitals->respiratory_rate ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->oxygen_saturation)
                        <li>O₂ Sat: {{ $prescription->vitals->oxygen_saturation ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->weight)
                        <li>Wt: {{ $prescription->vitals->weight ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->height)
                        <li>Ht: {{ $prescription->vitals->height ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->bmi)
                        <li>BMI: {{ $prescription->vitals->bmi ?? 'N/A' }}</li>
                        @endif
                        @if ($prescription->vitals->notes)
                        <li>Notes: {{ $prescription->vitals->notes ?? 'N/A' }}</li>
                        @endif
                    </ul>
                </div>
                @endif
                <!-- On Examinations -->
                @if ($prescription->onexaminations)
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">On Examination (O/Ex)</h2>
                    <ul class="list-disc list-inside space-y-1">
                        @forelse($prescription->onexaminations as $onexamination)
                            <li>{{ $onexamination['name'] ?? '-' }}</li>
                        @empty
                            <li>No Examination found</li>
                        @endforelse
                    </ul>
                </div>
                @endif
                
                <!-- Investigations -->
                @if ($prescription->investigations)
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">Investigation</h2>
                    <ul class="list-disc list-inside space-y-1">
                        @forelse($prescription->investigations as $investigation)
                            <li>{{ $investigation['name'] ?? '-' }}</li>
                        @empty
                            <li>No Investigation found</li>
                        @endforelse
                    </ul>
                </div>
                @endif

                <!-- Gyne. & Obs. History -->
                @if ($prescription->gynaeHistory)
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">Gyne. & Obs. History</h2>
                    <ul class="list-disc list-inside space-y-1">
                        @php
                            $history = $prescription->gynaeHistory->toArray();

                            // Fields to exclude
                            $exclude = ['id', 'prescription_id', 'created_at', 'updated_at'];
                        @endphp

                        @forelse($history as $key => $value)
                            @if (!in_array($key, $exclude) && !is_null($value) && $value !== '' && $value !== false)
                                <li>
                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                    @if (is_bool($value))
                                        {{ $value ? 'Yes' : 'No' }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </li>
                            @endif
                        @empty
                            <li>Gyne. & Obs. History not found</li>
                        @endforelse
                    </ul>
                </div>
                @endif



                <!-- diagnosis -->
                @if ($prescription->diagnosis)
                <div class="mb-3">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-1">Diagnosis</h2>
                    <ul class="list-disc list-inside space-y-1">
                        
                            <li>{{ $prescription->diagnosis ?? '-' }}</li>
                       
                    </ul>
                </div>
                @endif
            </aside>

            <!-- Main Content -->
            <section class="col-span-3">
                <div class="mb-4">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-2">Medicine</h2>
                    <table class="w-full border border-gray-300 text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Type</th>
                                <th class="border p-2">Medicine Name</th>
                                <th class="border p-2">Strength</th>
                                <th class="border p-2">Doses</th>
                                <th class="border p-2">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescription->medications as $med)
                                <tr>
                                    <td class="border p-2">{{ $med->type ?? '-'}}</td>
                                    <td class="border p-2">{{ $med->medicines->brand_name ?? '-' }}</td>
                                    <td class="border p-2">{{ $med->strength ?? '-' }}</td>
                                    <td class="border p-2">{{ $med->dosage ?? '-' }}<br>{{ $med->meal_time ?? '-'}}</td>
                                    <td class="border p-2">{{ $med->duration ?? '-' }}</td>
                                </tr>        
                                @if ($med->advice)                                
                                <tr>
                                    <td colspan="5" class="border p-2 col-span-2">Advice: {{ $med->advice ?? '-'}}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-2">Advice / Instructions</h2>
                    <ul class="list-disc list-inside space-y-1 instructions">
                        @foreach (explode("\n", $prescription->instructions) as $line)
                            @if (trim($line) !== "")
                                <li>{{ $line }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @if ($prescription->follow_up_advice)
                <div class="mb-4">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-2">Followup Advice</h2>
                    <p>{{ $prescription->follow_up_advice ?? '-' }}</p>
                </div>
                @endif
                <div class="mb-4">
                    <h2 class="text-base-color font-semibold border-b pb-1 mb-2">Followup Date</h2>
                    <p>{{ $prescription->followup_date->format('d M Y') }}</p>
                </div>
            </section>
        </div>
    </div>
    </div>

<!-- Wrapper at the very end of body -->
<div class="footer-area">
    <div class="signature text-right">
        <p class="font-semibold text-base-color">_________________________</p>
        <p>{{ $prescription->doctor?->name ?? '-' }}</p>
    </div>

    <footer class="print-footer w-full border-t-2 border-base mt-2 pt-2 text-center text-xs">
        {!! trim($prescription->footer_info) !!}
    </footer>

    <div class="poweredby text-right">
        <p class="text-base-color">Powered by {{ $appSettings['site_title'] ?? config('app.name') }}</p>
    </div>
</div>


</body>
</html>
