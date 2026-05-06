<?php

namespace App\Services\Erip;

use Carbon\Carbon;
use RuntimeException;
use ZipArchive;

class EripMonthlyReportParser
{
    /**
     * @return array{rows: array<int, array<string, mixed>>, report_month: string|null}
     */
    public function parse(string $xlsxPath): array
    {
        $sheetRows = $this->readFirstSheetRows($xlsxPath);
        $dataRows = $this->extractPaymentRows($sheetRows);

        $reportMonth = $this->detectReportMonth($xlsxPath, $sheetRows);
        $normalizedRows = [];

        foreach ($dataRows as $sourceRowNumber => $cols) {
            $amount = $this->toMoney($cols[1] ?? null);
            $netAmount = $this->toMoney($cols[2] ?? null);
            $paidAt = $this->toDateTime($cols[3] ?? null);
            $operationNumber = $this->normalizeOperationNumber($cols[4] ?? null);
            $payerRaw = trim((string) ($cols[6] ?? ''));
            [$payerPhone, $payerName] = $this->splitPayer($payerRaw);
            $invoiceCreatedAt = $this->toDateTime($cols[7] ?? null);

            if ($amount === null && !$paidAt && !$operationNumber) {
                continue;
            }

            $normalizedRows[] = [
                'row_number' => $sourceRowNumber,
                'status' => $this->nullableString($cols[0] ?? null),
                'amount' => $amount ?? 0.0,
                'net_amount' => $netAmount,
                'paid_at' => $paidAt,
                'operation_number' => $operationNumber,
                'payment_method' => $this->nullableString($cols[5] ?? null),
                'payer_raw' => $payerRaw ?: null,
                'payer_phone' => $payerPhone,
                'payer_name' => $payerName,
                'invoice_created_at' => $invoiceCreatedAt,
                'account_number' => $this->nullableString($cols[8] ?? null),
                'terminal_sn' => $this->nullableString($cols[9] ?? null),
                'merchant_code' => $this->nullableString($cols[10] ?? null),
                'raw_row' => $cols,
            ];
        }

        return [
            'rows' => $normalizedRows,
            'report_month' => $reportMonth,
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readFirstSheetRows(string $xlsxPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            throw new RuntimeException('Не удалось открыть XLSX файл.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            throw new RuntimeException('Не найден первый лист в XLSX файле.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml) {
            throw new RuntimeException('Некорректный формат XLSX файла.');
        }

        $rows = [];
        $rowNodes = $xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]');
        foreach ($rowNodes ?: [] as $row) {
            $rowIndex = (int) ($row['r'] ?? 0);
            $values = [];

            $cellNodes = $row->xpath('./*[local-name()="c"]');
            foreach ($cellNodes ?: [] as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $colIndex = $this->columnIndexFromCellRef($ref);
                $cellType = (string) ($cell['t'] ?? '');
                $v = $cell->xpath('./*[local-name()="v"]');
                $rawValue = isset($v[0]) ? (string) $v[0] : '';

                if ($cellType === 's') {
                    $sharedIndex = (int) $rawValue;
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } else {
                    $value = $rawValue;
                }

                if ($colIndex >= 0) {
                    $values[$colIndex] = trim($value);
                }
            }

            if (!empty($values)) {
                ksort($values);
                $offset = min(array_keys($values));
                $normalized = [];
                foreach ($values as $index => $value) {
                    $normalized[$index - $offset] = $value;
                }
                $rows[$rowIndex] = $normalized;
            }
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xml) {
            return [];
        }

        $root = simplexml_load_string($xml);
        if (!$root) {
            return [];
        }

        $strings = [];
        $siNodes = $root->xpath('//*[local-name()="si"]');
        foreach ($siNodes ?: [] as $si) {
            $singleTextNode = $si->xpath('./*[local-name()="t"]');
            if (isset($singleTextNode[0])) {
                $strings[] = trim((string) $singleTextNode[0]);
                continue;
            }

            $text = '';
            $runTextNodes = $si->xpath('./*[local-name()="r"]/*[local-name()="t"]');
            foreach ($runTextNodes ?: [] as $runText) {
                $text .= (string) $runText;
            }
            $strings[] = trim($text);
        }

        return $strings;
    }

    /**
     * @param array<int, array<int, string>> $rows
     * @return array<int, array<int, string>>
     */
    private function extractPaymentRows(array $rows): array
    {
        $result = [];

        foreach ($rows as $rowNumber => $cols) {
            $amount = $this->toMoney($cols[1] ?? null);
            $paidAt = $this->toDateTime($cols[3] ?? null);

            if ($amount === null || !$paidAt) {
                continue;
            }

            $result[$rowNumber] = $cols;
        }

        return $result;
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function detectReportMonth(string $xlsxPath, array $rows): ?string
    {
        $basename = pathinfo($xlsxPath, PATHINFO_FILENAME);
        if (preg_match('/(\d{4})-(\d{2})/', $basename, $m)) {
            return sprintf('%s-%s-01', $m[1], $m[2]);
        }

        foreach ($rows as $cols) {
            foreach ($cols as $text) {
                if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', $text, $m)) {
                    return sprintf('%s-%s-01', $m[3], $m[2]);
                }
            }
        }

        return null;
    }

    private function columnIndexFromCellRef(string $cellRef): int
    {
        if ($cellRef === '') {
            return -1;
        }

        if (!preg_match('/^([A-Z]+)/', $cellRef, $m)) {
            return -1;
        }

        $letters = $m[1];
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function toMoney(?string $raw): ?float
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $value = str_replace(',', '.', trim($raw));
        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function toDateTime(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse(trim($raw))->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullableString(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);
        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeOperationNumber(?string $raw): ?string
    {
        $value = $this->nullableString($raw);
        if ($value === null) {
            return null;
        }

        if (preg_match('/^[0-9]+(\.[0-9]+)?E[+\-]?[0-9]+$/i', $value)) {
            return sprintf('%.0f', (float) $value);
        }

        return $value;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function splitPayer(string $payerRaw): array
    {
        if ($payerRaw === '') {
            return [null, null];
        }

        if (preg_match('/^(\+?\d[\d\s\-()]{8,})\s+(.*)$/u', $payerRaw, $m)) {
            $phone = preg_replace('/\s+/', '', $m[1]);
            $name = trim($m[2]);
            return [$phone ?: null, $name !== '' ? $name : null];
        }

        return [null, $payerRaw];
    }
}
