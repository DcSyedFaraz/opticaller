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
        'mobile_number' => ['mobile'],
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
            'excel_file' => 'required|file|max:10240',
            'preview_data' => 'required|json',
            'options' => 'nullable|json',
        ]);

        $previewData = json_decode($payload['preview_data'], true);
        // dd($previewData); // For debugging purposes, remove in production

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
        $existingEmails = Address::pluck('email_address_system')->filter()->flip()->all();
        $existingPhones = Address::pluck('phone_number')->filter()->flip()->all();
        $existingMobiles = Address::pluck('mobile_number')->filter()->flip()->all();

        // 3) Preload subprojects for both ID and name lookups
        $subprojects = SubProject::pluck('id', 'title')->all();
        $subprojectsById = SubProject::pluck('id', 'id')->all();

        DB::transaction(function () use ($data, $options, &$imported, &$skipped, &$errors, $existingByStreetPostal, $existingByNamePostal, $existingEmails, $existingPhones, $existingMobiles, $subprojects, $subprojectsById) {
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
                    $validation = $this->validateRowData($normalized, $row, $rowNumber, $subprojects, $subprojectsById);
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
                            $existingMobiles,
                            $rowNumber,
                            $row
                        );
                        if (!$duplicateCheck['valid']) {
                            $errors = array_merge($errors, $duplicateCheck['errors']);
                            $skipped++;
                            continue; // Skip to next row
                        }
                    }

                    // Recover previously deleted records when re-imported
                    if ($trashed = $this->findTrashedMatch($normalized)) {
                        $updateData = $this->prepareInsert($normalized, $subprojects, $subprojectsById);
                        $trashed->restore();
                        $trashed->update($updateData);
                        $imported++;

                        if (!empty($updateData['street_address']) && !empty($updateData['postal_code'])) {
                            $existingByStreetPostal["{$updateData['street_address']}|{$updateData['postal_code']}"] = true;
                        }
                        if (!empty($updateData['company_name']) && !empty($updateData['postal_code'])) {
                            $existingByNamePostal["{$updateData['company_name']}|{$updateData['postal_code']}"] = true;
                        }
                        if (!empty($updateData['email_address_system'])) {
                            $existingEmails[$updateData['email_address_system']] = true;
                        }
                        if (!empty($updateData['phone_number'])) {
                            $existingPhones[$updateData['phone_number']] = true;
                        }
                        if (!empty($updateData['mobile_number'])) {
                            $existingMobiles[$updateData['mobile_number']] = true;
                        }

                        continue; // Skip normal insert path
                    }

                    // Prepare the insert data array
                    $toInsert[] = $this->prepareInsert($normalized, $subprojects, $subprojectsById);
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
                        if (!empty($record['mobile_number'])) {
                            $existingMobiles[$record['mobile_number']] = true;
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
    private function validateRowData(array $normalizedRow, array $rawRow, int $num, array $subprojects, array $subprojectsById): array
    {
        $errs = [];

        // company_name is optional

        // email_address_system if present must be a valid email
        if (
            !empty($normalizedRow['email_address_system'])
            && !filter_var($normalizedRow['email_address_system'], FILTER_VALIDATE_EMAIL)
        ) {
            $errs[] = "Row {$num}: Invalid email format ({$normalizedRow['email_address_system']})";
        }

        // follow_up_date if present must parse as a date
        if (!empty($normalizedRow['follow_up_date']) && !$this->parseDate($normalizedRow['follow_up_date'])) {
            $errs[] = "Row {$num}: Invalid date format ({$normalizedRow['follow_up_date']})";
        }

        // sub_project_id can be either an ID or a name; check existence
        if (!empty($normalizedRow['sub_project_id'])) {
            $subProjectValue = $normalizedRow['sub_project_id'];
            $isNumeric = is_numeric($subProjectValue);

            if ($isNumeric) {
                // Check if ID exists
                if (!isset($subprojectsById[$subProjectValue])) {
                    $errs[] = "Row {$num}: Subproject ID '{$subProjectValue}' does not exist";
                }
            } else {
                // Check if name exists
                if (!isset($subprojects[$subProjectValue])) {
                    $errs[] = "Row {$num}: Subproject name '{$subProjectValue}' does not exist";
                }
            }
        }

        // deal_id and contact_id, if present, must be numeric
        foreach (['deal_id', 'contact_id'] as $field) {
            if (!empty($normalizedRow[$field]) && !is_numeric($normalizedRow[$field])) {
                $errs[] = "Row {$num}: {$field} must be numeric ({$normalizedRow[$field]})";
            }
        }

        // company_name length limit
        if (!empty($normalizedRow['company_name']) && strlen($normalizedRow['company_name']) > 255) {
            $errs[] = "Row {$num}: Company name too long";
        }

        // feedback length limit
        if (!empty($normalizedRow['feedback']) && strlen($normalizedRow['feedback']) > 1000) {
            $errs[] = "Row {$num}: Feedback too long";
        }

        return [
            'valid' => empty($errs),
            'errors' => $this->createErrorEntries($num, $errs, $rawRow, $normalizedRow),
        ];
    }

    private function createErrorEntries(int $rowNumber, array $messages, array $rawRow, array $normalizedRow): array
    {
        if (empty($messages)) {
            return [];
        }

        return array_map(
            function ($message) use ($rowNumber, $rawRow, $normalizedRow) {
                return [
                    'rowNumber' => $rowNumber,
                    'message' => $message,
                    'rawRow' => $rawRow,
                    'normalizedRow' => $normalizedRow,
                ];
            },
            $messages
        );
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
        array &$mobiles,
        int $num,
        array $rawRow
    ): array {
        $errs = [];

        // If forbidden_promotion is flagged, skip entirely (special handling)
        if (!empty($row['forbidden_promotion'])) {
            $errs[] = "Row {$num}: Marked forbidden_promotion, not imported";
            return [
                'valid' => false,
                'errors' => $this->createErrorEntries($num, $errs, $rawRow, $row),
            ];
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

        // mobile_number duplicates
        if (!empty($row['mobile_number']) && isset($mobiles[$row['mobile_number']])) {
            $errs[] = "Row {$num}: Mobile number '{$row['mobile_number']}' already exists";
        }

        return [
            'valid' => empty($errs),
            'errors' => $this->createErrorEntries($num, $errs, $rawRow, $row),
        ];
    }

    /**
     * Find a soft-deleted address matching the given row using the same
     * duplicate rules.
     */
    private function findTrashedMatch(array $row): ?Address
    {
        if (!empty($row['contact_id'])) {
            $match = Address::onlyTrashed()->where('contact_id', $row['contact_id'])->first();
            if ($match) {
                return $match;
            }
        }

        if (!empty($row['street_address']) && !empty($row['postal_code'])) {
            $match = Address::onlyTrashed()
                ->where('street_address', $row['street_address'])
                ->where('postal_code', $row['postal_code'])
                ->first();
            if ($match) {
                return $match;
            }
        }

        if (!empty($row['company_name']) && !empty($row['postal_code'])) {
            $match = Address::onlyTrashed()
                ->where('company_name', $row['company_name'])
                ->where('postal_code', $row['postal_code'])
                ->first();
            if ($match) {
                return $match;
            }
        }

        if (!empty($row['email_address_system'])) {
            $match = Address::onlyTrashed()->where('email_address_system', $row['email_address_system'])->first();
            if ($match) {
                return $match;
            }
        }

        if (!empty($row['phone_number'])) {
            $match = Address::onlyTrashed()->where('phone_number', $row['phone_number'])->first();
            if ($match) {
                return $match;
            }
        }

        if (!empty($row['mobile_number'])) {
            $match = Address::onlyTrashed()->where('mobile_number', $row['mobile_number'])->first();
            if ($match) {
                return $match;
            }
        }

        return null;
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
    private function prepareInsert(array $row, array $subprojects, array $subprojectsById): array
    {
        // Resolve subproject ID or name → ID (if provided)
        $subId = null;
        if (!empty($row['sub_project_id'])) {
            $subProjectValue = $row['sub_project_id'];
            $isNumeric = is_numeric($subProjectValue);

            if ($isNumeric) {
                // It's an ID, use it directly
                $subId = (int) $subProjectValue;
            } else {
                // It's a name, look it up
                if (isset($subprojects[$subProjectValue])) {
                    $subId = $subprojects[$subProjectValue];
                }
            }
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
            'mobile_number' => $row['mobile_number'] ?? null,
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
