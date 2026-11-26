<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Generator Workbook Excel (.xlsx) Multi-Sheet Korporat berbasis PhpSpreadsheet:
 * - 100% sesuai standar Microsoft Excel (bebas peringatan repair)
 * - Banner Midnight Navy + aksen Emas, KPI Cards, Status Pill berwarna
 * - Grand Total dengan merge label + formula =SUM()
 */
class SimpleXlsxExporter
{
    /** @var array<int, array<string, mixed>> */
    protected array $sheets = [];

    public function addSheet(array $config): self
    {
        $this->sheets[] = $config;
        return $this;
    }

    protected function sanitizeSheetName(string $name): string
    {
        $clean = preg_replace('/[\\\\\/\?\*\[\]:]/', '', $name) ?? 'Sheet';
        return mb_substr($clean, 0, 31);
    }

    /**
     * @return array{bg:string, fg:string}
     */
    protected function statusColors(string $statusText): array
    {
        $s = mb_strtolower(trim($statusText));
        if (str_contains($s, 'diterima') || str_contains($s, 'selesai') || str_contains($s, 'terkirim') || str_contains($s, 'aman')) {
            return ['bg' => 'DCFCE7', 'fg' => '166534'];
        }
        if (str_contains($s, 'disetujui') || str_contains($s, 'dikonfirmasi') || str_contains($s, 'proses')) {
            return ['bg' => 'DBEAFE', 'fg' => '1E40AF'];
        }
        if (str_contains($s, 'draf') || str_contains($s, 'menunggu') || str_contains($s, 'revisi') || str_contains($s, 'menipis')) {
            return ['bg' => 'FEF3C7', 'fg' => '92400E'];
        }
        if (str_contains($s, 'batal') || str_contains($s, 'habis')) {
            return ['bg' => 'FEE2E2', 'fg' => '991B1B'];
        }
        return ['bg' => 'F1F5F9', 'fg' => '334155'];
    }

    public function buildBinary(): string
    {
        if (empty($this->sheets)) {
            throw new \RuntimeException('Minimal 1 sheet diperlukan untuk export XLSX.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Mini ERP PT Nusantara Distribusi')
            ->setTitle('Laporan Operasional PT Nusantara Distribusi');

        foreach ($this->sheets as $idx => $config) {
            $ws = $idx === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $ws->setTitle($this->sanitizeSheetName((string) ($config['name'] ?? 'Sheet ' . ($idx + 1))));
            $this->populateSheet($ws, $config);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }

    protected function populateSheet(Worksheet $ws, array $config): void
    {
        $title = (string) ($config['title'] ?? 'PT NUSANTARA DISTRIBUSI');
        $subtitle = (string) ($config['subtitle'] ?? '');
        $metaLine = (string) ($config['metaLine'] ?? '');
        $columns = (array) ($config['columns'] ?? []);
        $rows = (array) ($config['rows'] ?? []);
        $totalColumns = (array) ($config['totalColumns'] ?? []);
        $kpis = (array) ($config['kpis'] ?? []);
        $showGrid = (bool) ($config['showGridLines'] ?? true);

        $colCount = max(1, count($columns));
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        $ws->setShowGridLines($showGrid);
        $ws->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        // Lebar kolom
        foreach ($columns as $idx => $col) {
            $colLet = Coordinate::stringFromColumnIndex($idx + 1);
            $ws->getColumnDimension($colLet)->setWidth((int) ($col['width'] ?? 16));
        }

        $r = 1;

        // Row 1: Banner Midnight Navy
        $ws->setCellValue("A{$r}", $title);
        if ($colCount > 1) {
            $ws->mergeCells("A{$r}:{$lastCol}{$r}");
        }
        $ws->getRowDimension($r)->setRowHeight(34);
        $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $r++;

        // Row 2: Garis Aksen Emas
        $ws->getRowDimension($r)->setRowHeight(4);
        $ws->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F59E0B');
        $r++;

        // Row 3: Subtitle
        $ws->setCellValue("A{$r}", $subtitle);
        if ($colCount > 1) {
            $ws->mergeCells("A{$r}:{$lastCol}{$r}");
        }
        $ws->getRowDimension($r)->setRowHeight(20);
        $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '334155'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $r++;

        // Row 4: Meta Line
        $ws->setCellValue("A{$r}", $metaLine);
        if ($colCount > 1) {
            $ws->mergeCells("A{$r}:{$lastCol}{$r}");
        }
        $ws->getRowDimension($r)->setRowHeight(19);
        $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
            'font' => ['size' => 9.5, 'color' => ['rgb' => '475569'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $r++;

        // KPI Cards
        if (!empty($kpis)) {
            $ws->getRowDimension($r)->setRowHeight(10);
            $r++;

            $kpiLabelRow = $r;
            $kpiValRow = $r + 1;
            $ws->getRowDimension($kpiLabelRow)->setRowHeight(18);
            $ws->getRowDimension($kpiValRow)->setRowHeight(28);

            $kpiCount = count($kpis);
            $span = max(1, intdiv($colCount, $kpiCount));
            $colCursor = 1;

            foreach ($kpis as $kIdx => $kpi) {
                $startC = $colCursor;
                $endC = ($kIdx === $kpiCount - 1) ? $colCount : min($colCount, $startC + $span - 1);
                $startLet = Coordinate::stringFromColumnIndex($startC);
                $endLet = Coordinate::stringFromColumnIndex($endC);

                $ws->setCellValue("{$startLet}{$kpiLabelRow}", (string) $kpi['label']);
                if ($endC > $startC) {
                    $ws->mergeCells("{$startLet}{$kpiLabelRow}:{$endLet}{$kpiLabelRow}");
                }
                $ws->getStyle("{$startLet}{$kpiLabelRow}:{$endLet}{$kpiLabelRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '475569'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '2563EB']],
                        'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                        'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);

                $kType = $kpi['type'] ?? 'text';
                $kVal = $kType === 'int' ? (int) $kpi['value'] : ($kType === 'currency' ? (float) $kpi['value'] : (string) $kpi['value']);
                $ws->setCellValue("{$startLet}{$kpiValRow}", $kVal);
                if ($endC > $startC) {
                    $ws->mergeCells("{$startLet}{$kpiValRow}:{$endLet}{$kpiValRow}");
                }
                $valStyle = [
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                        'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                        'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ];
                if ($kType === 'currency') {
                    $valStyle['numberFormat'] = ['formatCode' => '"Rp "#,##0'];
                } elseif ($kType === 'int') {
                    $valStyle['numberFormat'] = ['formatCode' => '#,##0'];
                }
                $ws->getStyle("{$startLet}{$kpiValRow}:{$endLet}{$kpiValRow}")->applyFromArray($valStyle);

                $colCursor = $endC + 1;
            }
            $r += 2;
        }

        // Spacer
        $ws->getRowDimension($r)->setRowHeight(10);
        $r++;

        // Table Header
        $headerRow = $r;
        $ws->getRowDimension($headerRow)->setRowHeight(26);
        foreach ($columns as $idx => $col) {
            $colLet = Coordinate::stringFromColumnIndex($idx + 1);
            $ws->setCellValue("{$colLet}{$headerRow}", (string) $col['label']);
        }
        $ws->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0F172A']],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'F59E0B']],
            ],
        ]);
        $r++;

        // Freeze panes & AutoFilter
        $dataStartRow = $r;
        $ws->freezePane("A{$dataStartRow}");

        if (empty($rows)) {
            $ws->setCellValue("A{$r}", 'Tidak ada data pada periode ini.');
            if ($colCount > 1) {
                $ws->mergeCells("A{$r}:{$lastCol}{$r}");
            }
            $ws->getRowDimension($r)->setRowHeight(24);
            $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
            $lastDataRow = $r;
            $r++;
        } else {
            foreach ($rows as $rIdx => $row) {
                $isOdd = ($rIdx % 2 === 1);
                $ws->getRowDimension($r)->setRowHeight(22);
                $rowBg = $isOdd ? 'F8FAFC' : 'FFFFFF';

                $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '0F172A'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                foreach ($columns as $cIdx => $col) {
                    $colLet = Coordinate::stringFromColumnIndex($cIdx + 1);
                    $cellRef = "{$colLet}{$r}";
                    $val = $row[$cIdx] ?? '';
                    $type = $col['type'] ?? 'text';

                    if ($type === 'int') {
                        $ws->setCellValue($cellRef, (int) $val);
                        $ws->getStyle($cellRef)->getNumberFormat()->setFormatCode('#,##0');
                        $ws->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                    } elseif ($type === 'currency') {
                        $ws->setCellValue($cellRef, (float) $val);
                        $ws->getStyle($cellRef)->getNumberFormat()->setFormatCode('"Rp "#,##0');
                        $ws->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
                    } elseif ($type === 'status') {
                        $ws->setCellValue($cellRef, (string) $val);
                        $colors = $this->statusColors((string) $val);
                        $ws->getStyle($cellRef)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $colors['fg']]],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors['bg']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    } elseif ($type === 'center') {
                        $ws->setCellValue($cellRef, is_int($val) ? $val : (string) $val);
                        $ws->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    } else {
                        $ws->setCellValue($cellRef, (string) $val);
                        $ws->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
                    }
                }
                $lastDataRow = $r;
                $r++;
            }
        }

        $ws->setAutoFilter("A{$headerRow}:{$lastCol}{$lastDataRow}");

        // Grand Total Row
        if (!empty($rows) && !empty($totalColumns)) {
            $firstSumCol = min(array_keys($totalColumns));
            $ws->getRowDimension($r)->setRowHeight(25);
            $ws->setCellValue("A{$r}", 'GRAND TOTAL');
            if ($firstSumCol > 1) {
                $mergeEndLet = Coordinate::stringFromColumnIndex($firstSumCol);
                $ws->mergeCells("A{$r}:{$mergeEndLet}{$r}");
            }

            $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A'], 'name' => 'Calibri'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '2563EB']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '2563EB']],
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']],
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']],
                ],
            ]);

            foreach ($totalColumns as $cIdx => $tType) {
                $colLet = Coordinate::stringFromColumnIndex($cIdx + 1);
                $cellRef = "{$colLet}{$r}";
                $ws->setCellValue($cellRef, "=SUM({$colLet}{$dataStartRow}:{$colLet}{$lastDataRow})");
                $ws->getStyle($cellRef)->getAlignment()->setIndent(0);
                if ($tType === 'currency') {
                    $ws->getStyle($cellRef)->getNumberFormat()->setFormatCode('"Rp "#,##0');
                    $ws->getStyle($cellRef)->getFont()->setSize(11)->getColor()->setRGB('1D4ED8');
                } else {
                    $ws->getStyle($cellRef)->getNumberFormat()->setFormatCode('#,##0');
                }
            }
        }
    }
}
