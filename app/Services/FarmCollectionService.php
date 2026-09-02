<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class FarmCollectionService
{
    /**
     * Get a base query builder for farm collections.
     */
    public function query(): Builder
    {
        return FarmCollection::query();
    }

    /**
     * Get every collection recorded against the given farm, most recent
     * collection date first.
     */
    public function listForFarm(Farm $farm): Collection
    {
        return $this->query()
            ->where('farm_id', $farm->id)
            ->latest('collection_date')
            ->latest('id')
            ->get();
    }

    /**
     * Create a new farm collection record. A collection_code is always
     * server-generated — never accepted from the caller.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): FarmCollection
    {
        return FarmCollection::query()->create([
            ...$data,
            'collection_code' => $this->generateCollectionCode(),
        ]);
    }

    /**
     * Generate a unique, human-readable collection code (e.g.
     * COL-2026-AB12CD) — mirrors FarmService::generateFarmCode() /
     * BatchService::generateBatchNumber() exactly.
     */
    protected function generateCollectionCode(): string
    {
        do {
            $code = sprintf('COL-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (FarmCollection::query()->where('collection_code', $code)->exists());

        return $code;
    }

    /**
     * Update an existing farm collection record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(FarmCollection $collection, array $data): FarmCollection
    {
        $collection->update($data);

        return $collection;
    }

    /**
     * Delete a farm collection record.
     */
    public function delete(FarmCollection $collection): void
    {
        $collection->delete();
    }

    /**
     * Unit options for the collection form.
     *
     * @return array<int, string>
     */
    public function unitOptions(): array
    {
        return [
            'kg',
            'lbs',
            'bags',
        ];
    }

    /**
     * Payment status options for the collection form.
     *
     * @return array<int, string>
     */
    public function paymentStatusOptions(): array
    {
        return [
            'pending',
            'partial',
            'paid',
            'cancelled',
        ];
    }

    /**
     * Harvest season label options for the collection form.
     *
     * @return array<int, string>
     */
    public function harvestSeasonOptions(): array
    {
        return [
            'Main Crop',
            'Fly Crop',
            'Early Harvest',
            'Late Harvest',
        ];
    }

    /**
     * The validation rules for a collection's importable fields — shared
     * by the spreadsheet import so it enforces the same data shape as
     * the Add/Edit Collection form. `coffee_type` isn't required here:
     * a row that omits it falls back to the farm's own coffee_type, the
     * same default the form silently applies.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'collection_date' => ['required', 'date', 'before_or_equal:today'],
            'coffee_type' => ['nullable', 'string', 'max:100'],
            'variety' => ['nullable', 'string', 'max:255'],
            'harvest_season' => ['nullable', 'string', 'max:255', Rule::in($this->harvestSeasonOptions())],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:20', Rule::in($this->unitOptions())],
            'initial_moisture' => ['nullable', 'numeric', 'between:0,100'],
            'initial_defects' => ['nullable', 'numeric', 'min:0'],
            'initial_grade' => ['nullable', 'string', 'max:100'],
            'initial_quality_score' => ['nullable', 'numeric', 'between:0,100'],
            'collection_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_status' => ['nullable', 'string', 'max:50', Rule::in($this->paymentStatusOptions())],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Import collections from parsed spreadsheet rows against a farm.
     * Each row is validated independently — rows that fail are skipped
     * and reported back rather than aborting the whole import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, errors: array<int, array{row: int, errors: array<int, string>}>}
     */
    public function importRows(Farm $farm, array $rows, ?int $userId): array
    {
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($this->normalizeImportRow($row, $farm), $this->rules());

            if ($validator->fails()) {
                $errors[] = [
                    // +1 to move from a 0-index to a 1-index, +1 more for the header row.
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            $this->create([
                ...$validator->validated(),
                'farm_id' => $farm->id,
                'user_id' => $userId,
            ]);
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Normalize a raw spreadsheet row before validation: default
     * coffee_type/unit/currency/payment_status when blank, and convert
     * an Excel date serial for collection_date into a Y-m-d string.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeImportRow(array $row, Farm $farm): array
    {
        if (($row['coffee_type'] ?? '') === '') {
            $row['coffee_type'] = $farm->coffee_type;
        }

        if (($row['unit'] ?? '') === '') {
            $row['unit'] = 'kg';
        }

        if (($row['currency'] ?? '') === '') {
            $row['currency'] = 'USD';
        }

        if (($row['payment_status'] ?? '') === '') {
            $row['payment_status'] = 'pending';
        }

        if (isset($row['collection_date']) && is_numeric($row['collection_date'])) {
            try {
                $row['collection_date'] = ExcelDate::excelToDateTimeObject($row['collection_date'])->format('Y-m-d');
            } catch (Throwable) {
                // Leave the raw value in place; validation will report it.
            }
        }

        foreach (['variety', 'harvest_season', 'initial_grade', 'reference', 'notes'] as $optional) {
            if (($row[$optional] ?? null) === '') {
                $row[$optional] = null;
            }
        }

        return $row;
    }
}
