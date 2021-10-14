<?php

namespace Mediconesystems\LivewireDatatables\Exports;

use Maatwebsite\Excel\Excel as ExcelExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DatatableExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnWidths, WithStyles
{
    use Exportable;

    public $collection;
    public $fileName = 'DatatableExport';
    public $fileType = 'xlsx';
    public $styles = [];
    public $columnWidths = [];

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return array_keys((array) $this->collection->first());
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileType($fileType)
    {
        $this->fileType = strtolower($fileType);

        return $this;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function setColumnWidths($columnWidths)
    {
        $this->columnWidths = $columnWidths;

        return $this;
    }

    public function getColumnWidths(): array
    {
        return $this->columnWidths;
    }

    public function columnWidths(): array
    {
        return $this->getColumnWidths();
    }

    public function setStyles($styles)
    {
        $this->styles = $styles;

        return $this;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function styles(Worksheet $sheet)
    {
        return $this->getStyles();
    }

    public function getFileWriter($fileType)
    {
        switch ($fileType) {
            case "xlsx":
                $writer = ExcelExport::XLSX;
                break;
            case "csv":
                $writer = ExcelExport::CSV;
                break;
            case "tsv":
                $writer = ExcelExport::TSV;
                break;
            case "ods":
                $writer = ExcelExport::ODS;
                break;
            case "xls":
                $writer = ExcelExport::XLS;
                break;
            case "html":
                $writer = ExcelExport::HTML;
                break;
            case "mpdf":
                $writer = ExcelExport::MPDF;
                break;
            case "dompdf":
                $writer = ExcelExport::DOMPDF;
                break;
            case "tcpdf":
                $writer = ExcelExport::TCPDF;
                break;
            default:
                $writer = ExcelExport::XLSX;
        }

        return $writer;
    }

    public function download()
    {
        $fileName = $this->getFileName();
        $fileType = $this->getFileType();

        $writer = $this->getFileWriter($fileType);
        $headers = ($fileType === 'csv') ? ['Content-Type' => 'text/csv'] : [];

        return Excel::download($this, $fileName . '.' . $fileType, $writer, $headers);
    }
}
