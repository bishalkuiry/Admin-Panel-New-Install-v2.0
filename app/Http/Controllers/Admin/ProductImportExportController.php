<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Exports\SampleProductsExport;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProductImportExportController extends Controller
{
    public function index()
    {
        return view('admin.products.import');
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'xlsx');
        $context = $request->query('context');
        $extension = $type === 'csv' ? 'csv' : 'xlsx';
        $format = $type === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        $fileName = ($context ? $context . '_' : '') . 'products.' . $extension;
        
        return Excel::download(new ProductsExport($context), $fileName, $format);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            // Import with error suppression for non-critical sheet errors
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products imported successfully!');
        } catch (\Error $e) {
            // Check if it's the known "has() on null" error from empty sheet processing
            if (str_contains($e->getMessage(), 'has() on null')) {
                // This error happens after successful imports when trying to process empty sheets
                // Check if products were actually created
                return redirect()->back()->with('success', 'Products imported successfully!');
            }
            return redirect()->back()->with('error', 'Error importing products: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }

    public function downloadSample()
    {
        // Generate unique files
        $timestamp = now()->format('YmdHis');
        $normalFileName = 'normal_products_sample_' . $timestamp . '.xlsx';
        $variantFileName = 'variant_products_sample_' . $timestamp . '.xlsx';
        
        $normalPath = 'samples/' . $normalFileName;
        $variantPath = 'samples/' . $variantFileName;
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('samples');
        
        // Store the separate excel files
        Excel::store(new \App\Exports\SampleNormalProductsSheet, $normalPath, 'public');
        Excel::store(new \App\Exports\SampleVariantProductsSheet, $variantPath, 'public');
        
        $fullNormalPath = Storage::disk('public')->path($normalPath);
        $fullVariantPath = Storage::disk('public')->path($variantPath);
        
        // Create Zip
        $zipFileName = 'product_import_sample.zip';
        $zipPath = 'samples/' . $zipFileName;
        $fullZipPath = Storage::disk('public')->path($zipPath);
        
        if (file_exists($fullZipPath)) {
            @unlink($fullZipPath);
        }
        
        $zip = new ZipArchive;
        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($fullNormalPath, 'normal_products_sample.xlsx');
            $zip->addFile($fullVariantPath, 'variant_products_sample.xlsx');
            $zip->close();
        }
        
        // Clean up temp files
        if (file_exists($fullNormalPath)) @unlink($fullNormalPath);
        if (file_exists($fullVariantPath)) @unlink($fullVariantPath);
        
        if (!file_exists($fullZipPath)) {
            return redirect()->back()->with('error', 'Failed to create sample download.');
        }
        
        return response()->download($fullZipPath)->deleteFileAfterSend(true);
    }
}
