<?php
namespace App\Services;

use Carbon\Carbon;

class AddressDataValidator
{
    public static function parseDate($value): ?string
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

    public static function validateRowData(array $row, int $num, array $subprojectIds): array
    {
        $errs = [];

        if (empty($row['company_name'])) {
            $errs[] = "Row {$num}: Company name is required";
        }

        if (!empty($row['email_address_system']) && !filter_var($row['email_address_system'], FILTER_VALIDATE_EMAIL)) {
            $errs[] = "Row {$num}: Invalid email format ({$row['email_address_system']})";
        }

        if (!empty($row['follow_up_date']) && !self::parseDate($row['follow_up_date'])) {
            $errs[] = "Row {$num}: Invalid date format ({$row['follow_up_date']})";
        }

        if (!empty($row['sub_project_id']) && !in_array($row['sub_project_id'], $subprojectIds)) {
            $errs[] = "Row {$num}: Subproject '{$row['sub_project_id']}' does not exist";
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

    public static function checkDuplicates(
        array $row,
        array &$byStreetPostal,
        array &$byNamePostal,
        array &$emails,
        array &$phones,
        int $num
    ): array {
        $errs = [];

        if (!empty($row['forbidden_promotion'])) {
            $errs[] = "Row {$num}: Marked forbidden_promotion, not imported";
            return ['valid' => false, 'errors' => $errs];
        }

        if (!empty($row['street_address']) && !empty($row['postal_code'])) {
            $key1 = "{$row['street_address']}|{$row['postal_code']}";
            if (isset($byStreetPostal[$key1])) {
                $errs[] = "Row {$num}: Street '{$row['street_address']}' and postal_code '{$row['postal_code']}' combination already exists";
            }
        }

        if (!empty($row['company_name']) && !empty($row['postal_code'])) {
            $key2 = "{$row['company_name']}|{$row['postal_code']}";
            if (isset($byNamePostal[$key2])) {
                $errs[] = "Row {$num}: Company '{$row['company_name']}' and postal_code '{$row['postal_code']}' combination already exists";
            }
        }

        if (!empty($row['email_address_system']) && isset($emails[$row['email_address_system']])) {
            $errs[] = "Row {$num}: Email '{$row['email_address_system']}' already exists";
        }

        if (!empty($row['phone_number']) && isset($phones[$row['phone_number']])) {
            $errs[] = "Row {$num}: Phone number '{$row['phone_number']}' already exists";
        }

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    public static function prepareInsert(array $row): array
    {
        return [
            'company_name' => $row['company_name'] ?? null,
            'salutation' => $row['salutation'] ?? null,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'street_address' => $row['street_address'] ?? null,
            'postal_code' => $row['postal_code'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'website' => $row['website'] ?? null,
            'phone_number' => $row['phone_number'] ?? null,
            'email_address_system' => $row['email_address_system'] ?? null,
            'email_address_new' => $row['email_address_new'] ?? null,
            'feedback' => $row['feedback'] ?? null,
            'follow_up_date' => self::parseDate($row['follow_up_date'] ?? null),
            'linkedin' => $row['linkedin'] ?? null,
            'logo' => $row['logo'] ?? null,
            'notes' => $row['notes'] ?? null,
            'contact_id' => is_numeric($row['contact_id'] ?? null) ? (int) $row['contact_id'] : null,
            'sub_project_id' => $row['sub_project_id'] ?? null,
            'hubspot_tag' => $row['hubspot_tag'] ?? null,
            'deal_id' => is_numeric($row['deal_id'] ?? null) ? (int) $row['deal_id'] : null,
            'company_id' => $row['company_id'] ?? null,
            'titel' => $row['titel'] ?? null,
            'main_category_query' => $row['main_category_query'] ?? null,
            'sub_category_category' => $row['sub_category_category'] ?? null,
            'forbidden_promotion' => !empty($row['forbidden_promotion']) ? 1 : 0,
        ];
    }
}
