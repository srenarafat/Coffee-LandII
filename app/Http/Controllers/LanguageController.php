<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
{
        $locale = $request->input('locale');

    if (in_array($locale, ['en', 'kh'])) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}