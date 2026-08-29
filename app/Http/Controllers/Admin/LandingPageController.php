<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class LandingPageController extends Controller
{
    public function index()
    {
        $content = Setting::get('landing_page_content');

        if (!$content) {
            $path = resource_path('views/landing.blade.php');
            if (File::exists($path)) {
                $content = File::get($path);
            }
        }

        return view('admin.landing.index', compact('content'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'content' => 'required',
        ]);

        Setting::set('landing_page_content', $request->content, 'landing');

        return back()->with('success', 'Landing page content updated successfully');
    }
}
