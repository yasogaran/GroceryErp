<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ReportExportService
{
    /**
     * Generate CSV export from data
     *
     * @param Collection|array $data
     * @param array $headers
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToCSV($data, array $headers, string $filename)
    {
        $filename = $filename . '_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add headers
            fputcsv($file, $headers);

            // Add data rows
            foreach ($data as $row) {
                if (is_object($row)) {
                    $row = (array) $row;
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Generate Excel-compatible CSV with multiple sheets (separate files in ZIP)
     *
     * @param array $sheets ['sheet_name' => ['headers' => [], 'data' => []]]
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToExcel(array $sheets, string $filename)
    {
        // For simplicity, if single sheet, return CSV
        if (count($sheets) === 1) {
            $sheet = reset($sheets);
            return $this->exportToCSV($sheet['data'], $sheet['headers'], $filename);
        }

        // For multiple sheets, create ZIP with multiple CSV files
        $filename = $filename . '_' . now()->format('Y-m-d_His') . '.zip';

        $callback = function() use ($sheets) {
            $zip = new \ZipArchive();
            $tempFile = tempnam(sys_get_temp_dir(), 'report');

            if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($sheets as $sheetName => $sheetData) {
                    $csv = fopen('php://temp', 'r+');

                    // Add UTF-8 BOM
                    fprintf($csv, chr(0xEF).chr(0xBB).chr(0xBF));

                    // Add headers
                    fputcsv($csv, $sheetData['headers']);

                    // Add data
                    foreach ($sheetData['data'] as $row) {
                        if (is_object($row)) {
                            $row = (array) $row;
                        }
                        fputcsv($csv, $row);
                    }

                    rewind($csv);
                    $zip->addFromString($sheetName . '.csv', stream_get_contents($csv));
                    fclose($csv);
                }

                $zip->close();
            }

            readfile($tempFile);
            unlink($tempFile);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Format currency for export
     */
    public function formatCurrency($amount): string
    {
        return number_format((float)$amount, 2, '.', '');
    }

    /**
     * Format date for export
     */
    public function formatDate($date, string $format = 'Y-m-d'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date ? $date->format($format) : '';
    }

    /**
     * Format datetime for export
     */
    public function formatDateTime($datetime, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $datetime ? $datetime->format($format) : '';
    }
}
