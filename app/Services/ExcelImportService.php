<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExcelImportService
{
    /**
     * Parse CSV file and return rows
     *
     * @param string $filePath
     * @return array
     */
    public function parseCSV(string $filePath): array
    {
        $rows = [];
        $headers = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read headers
            if (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $headers = $data;
            }

            // Read data rows
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }

        return $rows;
    }

    /**
     * Validate imported rows
     *
     * @param array $rows
     * @param array $rules
     * @return array ['valid' => [], 'invalid' => []]
     */
    public function validateRows(array $rows, array $rules): array
    {
        $valid = [];
        $invalid = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, $rules);

            if ($validator->fails()) {
                $invalid[] = [
                    'row_number' => $index + 2, // +2 because of header row and 0-index
                    'data' => $row,
                    'errors' => $validator->errors()->all()
                ];
            } else {
                $valid[] = [
                    'row_number' => $index + 2,
                    'data' => $row
                ];
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid
        ];
    }

    /**
     * Generate CSV template file
     *
     * @param array $headers
     * @param array $sampleData (optional)
     * @param string $filename
     * @return string File path
     */
    public function generateTemplate(array $headers, array $sampleData = [], string $filename = 'template.csv'): string
    {
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $filePath = $tempPath . '/' . $filename;
        $handle = fopen($filePath, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        fputcsv($handle, $headers);

        // Write sample data if provided
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $filePath;
    }

    /**
     * Clean up temporary file
     *
     * @param string $filePath
     * @return void
     */
    public function cleanup(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Format validation results for display
     *
     * @param array $validationResults
     * @return array
     */
    public function formatValidationResults(array $validationResults): array
    {
        return [
            'total_rows' => count($validationResults['valid']) + count($validationResults['invalid']),
            'valid_count' => count($validationResults['valid']),
            'invalid_count' => count($validationResults['invalid']),
            'valid_rows' => $validationResults['valid'],
            'invalid_rows' => $validationResults['invalid']
        ];
    }
}
