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
    private const COLUMN_MAPPING = [
        'company_name' => ['company_name', 'company', 'companyname', 'company name'],
        'subproject_title' => ['subproject_title', 'subproject', 'sub_project', 'sub project', 'project'],
        'email_address_system' => ['email_address_system', 'email', 'email_address', 'email address', 'system_email'],
        'feedback' => ['feedback', 'last_feedback', 'comment', 'notes'],
        'follow_up_date' => ['follow_up_date', 'followup_date', 'follow up date', 'date', 'followup'],
        'deal_id' => ['deal_id', 'deal', 'deal_number', 'deal number'],
        'contact_id' => ['contact_id', 'contact', 'contact_number', 'contact number'],
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
            return back()->with([
                'success' => true,
                'message' => 'Import completed successfully',
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
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

    private function processImportData(array $data, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // preload existing emails and company+subproject combos
        $existingEmails = Address::pluck('email_address_system')->flip()->all();
        $existingPairs = Address::join('sub_projects', 'addresses.subproject_id', '=', 'sub_projects.id')
            ->get(['company_name', 'sub_projects.title'])
            ->mapWithKeys(fn($r) => ["{$r->company_name}|{$r->title}" => true])
            ->all();

        // preload subproject titles → IDs
        $subprojects = SubProject::pluck('id', 'title')->all();

        DB::transaction(function () use ($data, $options, &$imported, &$skipped, &$errors, $existingEmails, $existingPairs, $subprojects) {
            foreach (array_chunk($data, 100) as $batchIndex => $batch) {
                $toInsert = [];

                foreach ($batch as $rowIndex => $row) {
                    $rowNumber = $batchIndex * 100 + $rowIndex + 1;

                    if ($options['skipEmptyRows'] && $this->isEmptyRow($row)) {
                        $skipped++;
                        continue;
                    }

                    $norm = $this->normalizeRowData($row);
                    $val = $this->validateRowData($norm, $rowNumber, $subprojects);
                    if (!$val['valid']) {
                        $errors = array_merge($errors, $val['errors']);
                        $skipped++;
                        continue;
                    }

                    if ($options['validateData']) {
                        $dup = $this->checkDuplicates($norm, $existingEmails, $existingPairs, $rowNumber);
                        if (!$dup['valid']) {
                            $errors = array_merge($errors, $dup['errors']);
                            $skipped++;
                            continue;
                        }
                    }

                    $toInsert[] = $this->prepareInsert($norm, $subprojects);
                }

                if ($toInsert) {
                    Address::insert($toInsert);
                    $imported += count($toInsert);
                    foreach ($toInsert as $rec) {
                        if ($rec['email_address_system']) {
                            $existingEmails[$rec['email_address_system']] = true;
                        }
                        $key = "{$rec['company_name']}|" . ($rec['subproject_id'] ? SubProject::find($rec['subproject_id'])->title : '');
                        $existingPairs[$key] = true;
                    }
                }
            }
        });

        return compact('imported', 'skipped', 'errors');
    }

    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== ''));
    }

    private function normalizeRowData(array $row): array
    {
        $out = [];
        foreach (self::COLUMN_MAPPING as $std => $aliases) {
            foreach ($aliases as $alias) {
                foreach ($row as $col => $val) {
                    if (strcasecmp($col, $alias) === 0) {
                        $out[$std] = $val;
                        break 2;
                    }
                }
            }
        }
        return $out;
    }

    private function validateRowData(array $row, int $num, array $subs): array
    {
        $errs = [];

        if (empty($row['company_name'])) {
            $errs[] = "Row {$num}: Company name is required";
        }

        if (
            !empty($row['email_address_system'])
            && !filter_var($row['email_address_system'], FILTER_VALIDATE_EMAIL)
        ) {
            $errs[] = "Row {$num}: Invalid email format ({$row['email_address_system']})";
        }

        if (
            !empty($row['follow_up_date'])
            && !$this->parseDate($row['follow_up_date'])
        ) {
            $errs[] = "Row {$num}: Invalid date format ({$row['follow_up_date']})";
        }

        if (
            !empty($row['subproject_title'])
            && !isset($subs[$row['subproject_title']])
        ) {
            $errs[] = "Row {$num}: Subproject '{$row['subproject_title']}' does not exist";
        }

        foreach (['deal_id', 'contact_id'] as $field) {
            if (!empty($row[$field]) && !is_numeric($row[$field])) {
                $errs[] = "Row {$num}: {$field} must be numeric ({$row[$field]})";
            }
        }

        if (!empty($row['company_name']) && strlen($row['company_name']) > 255) {
            $errs[] = "Row {$num}: Company name too long";
        }

        if (!empty($row['feedback']) && strlen($row['feedback']) > 1000) {
            $errs[] = "Row {$num}: Feedback too long";
        }

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    private function checkDuplicates(
        array $row,
        array &$emails,
        array &$pairs,
        int $num
    ): array {
        $errs = [];

        if (
            !empty($row['email_address_system'])
            && isset($emails[$row['email_address_system']])
        ) {
            $errs[] = "Row {$num}: Email '{$row['email_address_system']}' already exists";
        }

        if (!empty($row['company_name']) && !empty($row['subproject_title'])) {
            $key = "{$row['company_name']}|{$row['subproject_title']}";
            if (isset($pairs[$key])) {
                $errs[] = "Row {$num}: Company '{$row['company_name']}' already exists for subproject '{$row['subproject_title']}'";
            }
        }

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    private function parseDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
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

    private function prepareInsert(array $row, array $subs): array
    {
        $subId = $row['subproject_title']
            ? ($subs[$row['subproject_title']] ?? null)
            : null;

        return [
            'company_name' => $row['company_name'] ?? null,
            'subproject_id' => $subId,
            'email_address_system' => $row['email_address_system'] ?? null,
            'feedback' => $row['feedback'] ?? null,
            'follow_up_date' => $this->parseDate($row['follow_up_date'] ?? null),
            'deal_id' => is_numeric($row['deal_id'] ?? null) ? (int) $row['deal_id'] : null,
            'contact_id' => is_numeric($row['contact_id'] ?? null) ? (int) $row['contact_id'] : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['company_name', 'subproject_title', 'email_address_system', 'feedback', 'follow_up_date', 'deal_id', 'contact_id'];
        $sample = [
            ['ABC Company Ltd', 'Project Alpha', 'contact@abc-company.com', 'Initial contact made', '2024-12-01', '12345', '67890'],
            ['XYZ Corporation', 'Project Beta', 'info@xyz-corp.com', 'Follow-up required', '2024-12-15', '54321', '09876'],
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
