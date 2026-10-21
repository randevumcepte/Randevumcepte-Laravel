<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Sistem Yonetim > Salon Detay > Musterileri Excel'e Aktar.
 * Satirlar ve basliklar controller'da hazirlanip buraya verilir.
 */
class SalonMusterileriExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $rows;
    protected $headings;
    protected $title;

    public function __construct(array $rows, array $headings, $title = 'Müşteriler')
    {
        $this->rows     = $rows;
        $this->headings = $headings;
        $this->title    = $title;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
