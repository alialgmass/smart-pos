<?php

namespace Modules\Reports\Exports;

class ReportExport
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function generate(
        string $title,
        array $rows,
        array $headers,
        string $dateRange,
        string $tenantName,
    ): string {
        $timestamp = now()->format('Y-m-d H:i:s');

        $output = '';

        $output .= '# '.$title."\n";
        $output .= __('Tenant').': '.$tenantName."\n";
        $output .= __('Period').': '.$dateRange."\n";
        $output .= __('Generated').': '.$timestamp."\n";
        $output .= "\n";

        $output .= implode(',', array_map(fn (string $header): string => $this->escapeCsvField($header), $headers))."\n";

        foreach ($rows as $row) {
            $values = array_map(fn (string $key): string => $this->escapeCsvField((string) ($row[$key] ?? '')), array_keys($headers));
            $output .= implode(',', $values)."\n";
        }

        return $output;
    }

    private function escapeCsvField(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
