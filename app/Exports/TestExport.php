<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class TestExport implements FromCollection
{
    public function collection()
    {
        return new Collection([
            ['Ad', 'Soyad', 'Yaş'],
            ['Ahmet', 'Yılmaz', 30],
            ['Mehmet', 'Demir', 25]
        ]);
    }
}