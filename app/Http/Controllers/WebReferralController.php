<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class WebReferralController extends Controller
{
    /**
     * Show referral landing page with OpenGraph tags
     */
    public function show($code)
    {
        $user = User::where('referral_code', $code)->firstOrFail();

        return view('web.referral', compact('user', 'code'));
    }
}
