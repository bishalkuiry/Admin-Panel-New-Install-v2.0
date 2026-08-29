<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderBy('is_default', 'desc')->orderBy('name')->get();
        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code',
            'is_rtl' => 'boolean',
        ]);

        $validated['is_rtl'] = $request->has('is_rtl');
        $validated['is_active'] = true;

        Language::create($validated);

        return redirect()->back()->with('success', "Language '{$validated['name']}' added successfully!");
    }

    public function toggleRtl(Language $language)
    {
        $language->update(['is_rtl' => !$language->is_rtl]);
        return redirect()->back()->with('success', "RTL direction for {$language->name} updated!");
    }

    public function setDefault(Language $language)
    {
        Language::query()->update(['is_default' => false]);
        $language->update(['is_default' => true, 'is_active' => true]);

        return redirect()->back()->with('success', "{$language->name} set as Default System Language!");
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return redirect()->back()->with('error', 'Cannot delete default system language.');
        }

        $language->delete();
        return redirect()->back()->with('success', 'Language deleted!');
    }

    public function switchLanguage(Request $request, string $code)
    {
        $language = Language::where('code', $code)->where('is_active', true)->first();

        if ($language) {
            session(['locale' => $language->code, 'is_rtl' => $language->is_rtl]);
            app()->setLocale($language->code);
        }

        return redirect()->back();
    }
}
