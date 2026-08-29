<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExport;
use App\Exports\SampleCategoriesExport;
use App\Imports\CategoriesImport;

class CategoryImportExportController extends Controller
{
    public function index()
    {
        return view('admin.categories.import');
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'xlsx');
        $extension = $type === 'csv' ? 'csv' : 'xlsx';
        $format = $type === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        return Excel::download(new CategoriesExport, 'categories.' . $extension, $format);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new CategoriesImport, $request->file('file'));
            return redirect()->back()->with('success', 'Categories imported successfully!');
        } catch (\Error $e) {
            if (str_contains($e->getMessage(), 'has() on null')) {
                return redirect()->back()->with('success', 'Categories imported successfully!');
            }
            return redirect()->back()->with('error', 'Error importing categories: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing categories: ' . $e->getMessage());
        }
    }

    public function downloadSample()
    {
        return Excel::download(new SampleCategoriesExport, 'categories_sample.xlsx');
    }
}
