<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use App\Imports\NormalProductsImport;
use App\Imports\VariantProductsImport;

class ProductsImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        // Only map by sheet name - this prevents double-processing
        return [
            'Normal Products' => new NormalProductsImport(),
            'Variant Products' => new VariantProductsImport(),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Required to prevent errors on unknown sheets
        info("Skipping unknown sheet: {$sheetName}");
    }
}

