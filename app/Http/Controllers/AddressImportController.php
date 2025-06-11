<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\SubProject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AddressImportController extends Controller
{
    /**
     * Map incoming JSON keys (IMPORT) to database columns (existing table).
     */
    private const COLUMN_MAPPING = [
        // Core fields
        'company_name' => ['name'],
        'street_address' => ['street'],
        'postal_code' => ['postal_code'],
        'city' => ['city'],
        'country' => ['country'],
        'website' => ['site'],
        'phone_number' => ['phone'],
        'email_address_system' => ['email_1'],
        'notes' => ['vim_info'],
        // Retain feedback, follow-up, deal/contact, sub_project if present
        'feedback' => ['feedback', 'last_feedback', 'comment', 'notes_comment'],
        'follow_up_date' => ['follow_up_date', 'followup_date', 'follow up date', 'date', 'followup'],
        'deal_id' => ['deal_id', 'deal', 'deal_number', 'deal number'],
        'contact_id' => ['contact_id', 'contact', 'contact_number', 'contact number'],
        'sub_project_id' => ['subproject_title', 'sub_project_title', 'sub_project'],
        // New category fields
        'main_category_query' => ['query'],
        'sub_category_category' => ['category'],
        'forbidden_promotion' => ['forbidden_promotion'],
        // Drop these IMPORT keys entirely (they will not be mapped)
        // 'name_for_emails', 'subtypes', 'type', 'full_address', 'borough', 'country_code'
    ];

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $payload = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'preview_data' => 'required|json',
            'options' => 'nullable|json',
        ]);

        $previewData = json_decode($payload['preview_data'], true);

        if (empty($previewData)) {
            return back()->with([
                'success' => false,
                'message' => 'No data found in the uploaded file',
            ], 400);
        }

        $options = array_merge(
            ['skipEmptyRows' => true, 'validateData' => true],
            json_decode($payload['options'] ?? '{}', true) ?: []
        );

        try {
            $result = $this->processImportData($previewData, $options);
            // dd($result); // For debugging purposes, remove in production
            return back()->with([
                'success' => true,
                'message' => 'Import completed successfully',
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'importErrors' => $result['errors'],
                'total' => count($previewData),
            ]);
        } catch (\Throwable $e) {
            Log::error('Excel import failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'file' => $request->file('excel_file')?->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with([
                'success' => false,
                'message' => 'Import failed due to server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Please contact support',
            ], 500);
        }
    }

    /**
     * Process all rows of preview data and insert into `addresses` table.
     */
    private function processImportData(array $data, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // 1) Preload existing composite keys for duplicate checks:
        //    - street_address + postal_code  (Set 1)
        //    - company_name   + postal_code  (Set 2)
        $existingByStreetPostal = Address::get(['street_address', 'postal_code'])
            ->mapWithKeys(fn($r) => ["{$r->street_address}|{$r->postal_code}" => true])
            ->all();

        $existingByNamePostal = Address::get(['company_name', 'postal_code'])
            ->mapWithKeys(fn($r) => ["{$r->company_name}|{$r->postal_code}" => true])
            ->all();

        // 2) Preload existing emails and phones (optional)
        $existingEmails = Address::pluck('email_address_system')->flip()->all();
        $existingPhones = Address::pluck('phone_number')->flip()->all();

        // 3) Preload subprojects (if using sub_project_title to look up ID)
        $subprojects = SubProject::pluck('id', 'title')->all();

        DB::transaction(function () use ($data, $options, &$imported, &$skipped, &$errors, $existingByStreetPostal, $existingByNamePostal, $existingEmails, $existingPhones, $subprojects) {
            foreach (array_chunk($data, 100) as $batchIndex => $batch) {
                $toInsert = [];

                foreach ($batch as $rowIndex => $row) {
                    $rowNumber = $batchIndex * 100 + $rowIndex + 1;

                    // Skip entirely empty rows if requested
                    if ($options['skipEmptyRows'] && $this->isEmptyRow($row)) {
                        $skipped++;
                        continue;
                    }

                    // Normalize (map import keys → our columns)
                    $normalized = $this->normalizeRowData($row);

                    // Validate required fields and format
                    $validation = $this->validateRowData($normalized, $rowNumber, $subprojects);
                    if (!$validation['valid']) {
                        $errors = array_merge($errors, $validation['errors']);
                        $skipped++;
                        continue; // Skip to next row
                    }

                    if ($options['validateData']) {
                        // Check duplicates based on new rules:
                        // 1) street_address + postal_code
                        // 2) company_name   + postal_code
                        $duplicateCheck = $this->checkDuplicates(
                            $normalized,
                            $existingByStreetPostal,
                            $existingByNamePostal,
                            $existingEmails,
                            $existingPhones,
                            $rowNumber
                        );
                        if (!$duplicateCheck['valid']) {
                            $errors = array_merge($errors, $duplicateCheck['errors']);
                            $skipped++;
                            continue; // Skip to next row
                        }
                    }
                    // Prepare the insert data array
                    $toInsert[] = $this->prepareInsert($normalized, $subprojects);
                }
                // dd($toInsert);

                if (!empty($toInsert)) {
                    Address::insert($toInsert);
                    $imported += count($toInsert);

                    // After insert, update our in-memory sets so subsequent rows won't duplicate
                    foreach ($toInsert as $record) {
                        if (!empty($record['street_address']) && !empty($record['postal_code'])) {
                            $key1 = "{$record['street_address']}|{$record['postal_code']}";
                            $existingByStreetPostal[$key1] = true;
                        }
                        if (!empty($record['company_name']) && !empty($record['postal_code'])) {
                            $key2 = "{$record['company_name']}|{$record['postal_code']}";
                            $existingByNamePostal[$key2] = true;
                        }
                        if (!empty($record['email_address_system'])) {
                            $existingEmails[$record['email_address_system']] = true;
                        }
                        if (!empty($record['phone_number'])) {
                            $existingPhones[$record['phone_number']] = true;
                        }
                    }
                }
            }
        });

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Return true if all columns in $row are null or empty string.
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== ''));
    }

    /**
     * Normalize a single row: map incoming keys (case-insensitive) to our known columns.
     * Unmapped keys are ignored (i.e., certain IMPORT fields are dropped).
     */
    private function normalizeRowData(array $row): array
    {
        $out = [];

        foreach (self::COLUMN_MAPPING as $targetColumn => $aliases) {
            foreach ($aliases as $alias) {
                foreach ($row as $colName => $value) {
                    if (strcasecmp($colName, $alias) === 0) {
                        $out[$targetColumn] = $value;
                        break 2;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Validate each normalized row for required/format constraints.
     */
    private function validateRowData(array $row, int $num, array $subprojects): array
    {
        $errs = [];

        // company_name is required
        if (empty($row['company_name'])) {
            $errs[] = "Row {$num}: Company name is required";
        }

        // email_address_system if present must be a valid email
        if (
            !empty($row['email_address_system'])
            && !filter_var($row['email_address_system'], FILTER_VALIDATE_EMAIL)
        ) {
            $errs[] = "Row {$num}: Invalid email format ({$row['email_address_system']})";
        }

        // follow_up_date if present must parse as a date
        if (!empty($row['follow_up_date']) && !$this->parseDate($row['follow_up_date'])) {
            $errs[] = "Row {$num}: Invalid date format ({$row['follow_up_date']})";
        }

        // sub_project_id is actually passed as a title; check existence
        if (!empty($row['sub_project_id']) && !isset($subprojects[$row['sub_project_id']])) {
            $errs[] = "Row {$num}: Subproject '{$row['sub_project_id']}' does not exist";
        }

        // deal_id and contact_id, if present, must be numeric
        foreach (['deal_id', 'contact_id'] as $field) {
            if (!empty($row[$field]) && !is_numeric($row[$field])) {
                $errs[] = "Row {$num}: {$field} must be numeric ({$row[$field]})";
            }
        }

        // company_name length limit
        if (!empty($row['company_name']) && strlen($row['company_name']) > 255) {
            $errs[] = "Row {$num}: Company name too long";
        }

        // feedback length limit
        if (!empty($row['feedback']) && strlen($row['feedback']) > 1000) {
            $errs[] = "Row {$num}: Feedback too long";
        }

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    /**
     * Check for duplicates based on:
     *  - street_address + postal_code
     *  - company_name   + postal_code
     *  - email_address_system
     *  - phone_number
     */
    private function checkDuplicates(
        array $row,
        array &$byStreetPostal,
        array &$byNamePostal,
        array &$emails,
        array &$phones,
        int $num
    ): array {
        $errs = [];

        // If forbidden_promotion is flagged, skip entirely (special handling)
        if (!empty($row['forbidden_promotion'])) {
            $errs[] = "Row {$num}: Marked forbidden_promotion, not imported";
            return ['valid' => false, 'errors' => $errs];
        }

        // street_address + postal_code (Set 1)
        if (!empty($row['street_address']) && !empty($row['postal_code'])) {
            $key1 = "{$row['street_address']}|{$row['postal_code']}";
            if (isset($byStreetPostal[$key1])) {
                $errs[] = "Row {$num}: Street '{$row['street_address']}' and postal_code '{$row['postal_code']}' combination already exists";
            }
        }

        // company_name + postal_code (Set 2)
        if (!empty($row['company_name']) && !empty($row['postal_code'])) {
            $key2 = "{$row['company_name']}|{$row['postal_code']}";
            if (isset($byNamePostal[$key2])) {
                $errs[] = "Row {$num}: Company '{$row['company_name']}' and postal_code '{$row['postal_code']}' combination already exists";
            }
        }

        // email_address_system duplicates
        if (!empty($row['email_address_system']) && isset($emails[$row['email_address_system']])) {
            $errs[] = "Row {$num}: Email '{$row['email_address_system']}' already exists";
        }

        // phone_number duplicates
        if (!empty($row['phone_number']) && isset($phones[$row['phone_number']])) {
            $errs[] = "Row {$num}: Phone number '{$row['phone_number']}' already exists";
        }

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    /**
     * Convert Excel numeric or string dates into Y-m-d format, or return null if invalid.
     */
    private function parseDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                // Excel stores dates as days since 1900-01-00 (with a 2-day offset)
                $date = Carbon::create(1900, 1, 1)->addDays($value - 2);
                return $date->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Prepare the final array to insert into the `addresses` table.
     */
    private function prepareInsert(array $row, array $subprojects): array
    {
        // Resolve subproject title → ID (if provided)
        $subId = null;
        if (!empty($row['sub_project_id']) && isset($subprojects[$row['sub_project_id']])) {
            $subId = $subprojects[$row['sub_project_id']];
        }

        return [
            // Existing table columns:
            'company_name' => $row['company_name'] ?? null,
            'salutation' => null,
            'first_name' => null,
            'last_name' => null,
            'street_address' => $row['street_address'] ?? null,
            'postal_code' => $row['postal_code'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'website' => $row['website'] ?? null,
            'phone_number' => $row['phone_number'] ?? null,
            'email_address_system' => $row['email_address_system'] ?? null,
            'email_address_new' => $row['email_address_new'] ?? null,
            'feedback' => $row['feedback'] ?? null,
            'follow_up_date' => $this->parseDate($row['follow_up_date'] ?? null),
            'linkedin' => null,
            'logo' => null,
            'notes' => $row['notes'] ?? null,
            'contact_id' => is_numeric($row['contact_id'] ?? null) ? (int) $row['contact_id'] : null,
            'sub_project_id' => $subId,
            'created_at' => now(),
            'updated_at' => now(),
            'hubspot_tag' => null,
            'deal_id' => is_numeric($row['deal_id'] ?? null) ? (int) $row['deal_id'] : null,
            'company_id' => null,
            'titel' => null,
            // New category fields:
            'main_category_query' => $row['main_category_query'] ?? null,
            'sub_category_category' => $row['sub_category_category'] ?? null,
            'forbidden_promotion' => !empty($row['forbidden_promotion']) ? 1 : 0,
        ];
    }

    /**
     * Download a sample CSV template that matches our expected import headers.
     */
    public function downloadTemplate(): StreamedResponse
    {
        // dd('This method is deprecated. Please use the new template download functionality.');
        $headers = [
            'name',
            'street',
            'postal_code',
            'city',
            'country',
            'site',
            'phone',
            'email_1',
            'vim_info',
            'query',
            'category',
            'feedback',
            'sub_project',
            'forbidden_promotion',
        ];

        $sample = [
            [
                'Seniorenbüro Windeck e.V.',
                'Bergische Str. 30',
                '51570',
                'Windeck',
                'Germany',
                'https://seniorenbuero-windeck.de/',
                '+49 2292 3948795',
                'a.aberfeld@seniorenbuero-windeck.de',
                '',
                'Senioren Tagespflege, 51570, Windeck, Nordrhein-Westfalen, DE',
                'Club',
                'Initial contact made',
                '1',
                '0',
            ],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'address_import_template.csv', ['Content-Type' => 'text/csv']);
    }
}
