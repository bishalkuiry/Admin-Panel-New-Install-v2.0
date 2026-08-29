<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\SampleNormalProductsSheet;
use App\Exports\SampleVariantProductsSheet;

class SampleProductsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SampleNormalProductsSheet(),
            new SampleVariantProductsSheet(),
        ];
    }
}
