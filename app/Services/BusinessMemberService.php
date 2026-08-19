<?php

namespace App\Services;

use App\Models\BusinessMember;
use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class BusinessMemberService
{
    /**
     * The validation rules for a member's fields — shared between the
     * registration/edit form and spreadsheet imports so both enforce
     * exactly the same data shape.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,prefer_not_to_say'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'id_number' => ['required', 'string', 'max:100'],
            'designation' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get every member registered under a business, newest first.
     *
     * @return Collection<int, BusinessMember>
     */
    public function forBusiness(int $businessProfileId): Collection
    {
        return BusinessMember::query()
            ->where('business_profile_id', $businessProfileId)
            ->latest()
            ->get();
    }

    /**
     * Register a new member under a business.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(BusinessProfile $business, array $data): BusinessMember
    {
        return BusinessMember::query()->create([
            ...$data,
            'business_profile_id' => $business->id,
        ]);
    }

    /**
     * Update a registered member's details.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(BusinessMember $member, array $data): BusinessMember
    {
        $member->update($data);

        return $member;
    }

    /**
     * Remove a registered member.
     */
    public function destroy(BusinessMember $member): void
    {
        $member->delete();
    }

    /**
     * Import members from parsed spreadsheet rows. Each row is validated
     * independently against the same rules as the registration form —
     * rows that fail are skipped and reported back rather than aborting
     * the whole import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, errors: array<int, array{row: int, errors: array<int, string>}>}
     */
    public function importRows(BusinessProfile $business, array $rows): array
    {
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($this->normalizeImportRow($row), $this->rules());

            if ($validator->fails()) {
                $errors[] = [
                    // +1 to move from a 0-index to a 1-index, +1 more for the header row.
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            $this->create($business, $validator->validated());
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Normalize a raw spreadsheet row before validation: lowercase gender,
     * convert Excel date serials for date_of_birth into Y-m-d strings, and
     * treat blank optional fields as absent.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeImportRow(array $row): array
    {
        if (isset($row['gender']) && is_string($row['gender'])) {
            $row['gender'] = strtolower(trim($row['gender']));
        }

        if (isset($row['date_of_birth']) && is_numeric($row['date_of_birth'])) {
            try {
                $row['date_of_birth'] = ExcelDate::excelToDateTimeObject($row['date_of_birth'])->format('Y-m-d');
            } catch (Throwable) {
                // Leave the raw value in place; validation will report it.
            }
        }

        foreach (['email', 'address', 'notes'] as $optional) {
            if (($row[$optional] ?? null) === '') {
                $row[$optional] = null;
            }
        }

        return $row;
    }
}
