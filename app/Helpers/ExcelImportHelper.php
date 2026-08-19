<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportHelper
{
    /**
     * Read an uploaded spreadsheet's first sheet into an array of
     * associative rows, keyed by a normalized version of the header row
     * (e.g. "Date Of Birth" becomes "date_of_birth"). Fully blank rows are
     * skipped.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function readRows(UploadedFile $file): array
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if ($rows === []) {
            return [];
        }

        $headers = array_map(
            static fn (mixed $header): string => self::normalizeHeader((string) $header),
            array_shift($rows)
        );

        $records = [];

        foreach ($rows as $row) {
            if (self::isBlankRow($row)) {
                continue;
            }

            $record = [];

            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $value = $row[$index] ?? null;
                $record[$header] = is_string($value) ? trim($value) : $value;
            }

            $records[] = $record;
        }

        return $records;
    }

    /**
     * Normalize a header cell into a snake_case field key, e.g.
     * "Date Of Birth" becomes "date_of_birth".
     */
    private static function normalizeHeader(string $header): string
    {
        $header = str_replace(['-', '/'], ' ', trim($header));
        $header = (string) preg_replace('/\s+/', '_', $header);

        return strtolower($header);
    }

    /**
     * Whether every cell in a row is empty.
     *
     * @param  array<int, mixed>  $row
     */
    private static function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
