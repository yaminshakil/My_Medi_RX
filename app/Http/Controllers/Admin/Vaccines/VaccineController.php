<?php

namespace App\Http\Controllers\Admin\Vaccines;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use App\Services\Eprescriptions\MedicineService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VaccineController extends Controller
{
    protected MedicineService $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
        ]);

        $vaccines = Vaccine::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('manufacturer_id', 'like', "%{$search}%");
                });
            })
            ->when(
                ($filters['status'] ?? 'all') !== 'all',
                fn ($query) => $query->where(
                    'is_active',
                    ($filters['status'] ?? 'all') === 'active'
                )
            )->with('manufacturer')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $manufacturers = $this->medicineService->getManufacturer();

        $manufacturers = $manufacturers->map(function ($manufacturer) {
            return [
                'name' => $manufacturer->company_name,
                'id'   => $manufacturer->id,
            ];
        });

        return Inertia::render('Admin/Vaccines/Index', [
            'vaccines'      => $vaccines,
            'manufacturers' => $manufacturers,
            'filters'       => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vaccines', 'code'),
            ],
            'manufacturer_id' => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'is_active'       => ['required', 'boolean'],
        ]);

        Vaccine::create($data);

        return back()->with('success', 'Vaccine created successfully.');
    }

    public function update(Request $request, Vaccine $vaccine): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vaccines', 'code')->ignore($vaccine->id),
            ],
            'manufacturer_id' => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'is_active'       => ['required', 'boolean'],
        ]);

        $vaccine->update($data);

        return back()->with('success', 'Vaccine updated successfully.');
    }

    public function destroy(Vaccine $vaccine): RedirectResponse
    {
        $vaccine->delete();

        return back()->with('success', 'Vaccine deleted successfully.');
    }

    // Add this method inside VaccineController.

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120',
            ],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'Unable to read the uploaded CSV file.',
            ]);
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $seenCodes = [];
        $rowNumber = 0;
        $maxRows = 5000;

        try {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV file is empty.',
                ]);
            }

            // Remove UTF-8 BOM and normalize header names.
            $headers = array_map(
                fn ($header) => strtolower(trim(
                    preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)
                )),
                $headers
            );

            $requiredHeaders = ['name', 'code', 'manufacturer_id', 'description', 'is_active'];

            if (count($headers) !== count(array_unique($headers))) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV contains duplicate column headers.',
                ]);
            }

            if (! in_array('name', $headers, true)) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV must contain a name column.',
                ]);
            }

            $allowedHeaders = $requiredHeaders;

            foreach ($headers as $header) {
                if (! in_array($header, $allowedHeaders, true)) {
                    throw ValidationException::withMessages([
                        'file' => "Unsupported CSV column: {$header}.",
                    ]);
                }
            }

            while (($values = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($rowNumber > $maxRows) {
                    $errors[] = "Import stopped: maximum of {$maxRows} data rows reached.";
                    break;
                }

                // Ignore completely blank rows.
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (count($values) !== count($headers)) {
                    $skipped++;
                    $errors[] = 'Row '.($rowNumber + 1).': Column count does not match the header.';

                    continue;
                }

                $row = array_combine($headers, $values);

                $row = array_map(
                    fn ($value) => is_string($value) ? trim($value) : $value,
                    $row
                );

                $activeValue = strtolower((string) ($row['is_active'] ?? '1'));

                if (in_array($activeValue, ['1', 'true', 'yes'], true)) {
                    $isActive = true;
                } elseif (in_array($activeValue, ['0', 'false', 'no'], true)) {
                    $isActive = false;
                } else {
                    $skipped++;
                    $errors[] = 'Row '.($rowNumber + 1).': is_active must be 1/0, true/false, or yes/no.';

                    continue;
                }

                $data = [
                    'name'            => $row['name'] ?? '',
                    'code'            => ($row['code'] ?? '') !== '' ? $row['code'] : null,
                    'manufacturer_id' => ($row['manufacturer_id'] ?? '') !== ''
                        ? $row['manufacturer_id']
                        : null,
                    'description' => ($row['description'] ?? '') !== ''
                        ? $row['description']
                        : null,
                    'is_active' => $isActive,
                ];

                $validator = Validator::make($data, [
                    'name'            => ['required', 'string', 'max:255'],
                    'code'            => ['nullable', 'string', 'max:255'],
                    'manufacturer_id' => ['nullable', 'string', 'max:255'],
                    'description'     => ['nullable', 'string'],
                    'is_active'       => ['required', 'boolean'],
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    $errors[] = 'Row '.($rowNumber + 1).': '.
                        implode(' ', $validator->errors()->all());

                    continue;
                }

                $code = $data['code'];

                if ($code !== null) {
                    $normalizedCode = strtolower($code);

                    if (isset($seenCodes[$normalizedCode])) {
                        $skipped++;
                        $errors[] = 'Row '.($rowNumber + 1).
                            ": Duplicate code '{$code}' in this file.";

                        continue;
                    }

                    $seenCodes[$normalizedCode] = true;

                    if (DB::table('vaccines')->where('code', $code)->exists()) {
                        $skipped++;
                        $errors[] = 'Row '.($rowNumber + 1).
                            ": Vaccine code '{$code}' already exists.";

                        continue;
                    }
                }

                try {
                    Vaccine::create([
                        ...$data,
                        'uuid' => (string) Str::uuid(),
                    ]);

                    $imported++;
                } catch (QueryException $exception) {
                    // Protect against concurrent imports and database unique constraints.
                    $skipped++;
                    $errors[] = 'Row '.($rowNumber + 1).
                        ': Database rejected the row, possibly because a code already exists.';
                }
            }
        } finally {
            fclose($handle);
        }

        return back()->with('import_result', [
            'imported'          => $imported,
            'skipped'           => $skipped,
            'errors'            => array_slice($errors, 0, 100),
            'additional_errors' => max(0, count($errors) - 100),
        ]);
    }

    public function template()
    {
        $headers = [
            'name',
            'code',
            'manufacturer_id',
            'description',
            'is_active',
        ];

        $sample = [
            ['Hepatitis B', 'HEPB', 'MAN-001', 'Hepatitis B vaccine', '1'],
            ['BCG', 'BCG', 'MAN-002', 'BCG vaccine', '1'],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM helps Excel open the CSV correctly.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($sample as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'vaccine-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
