<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function store(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['fr', 'uk'])) {
            abort(404);
        }

        $request->user()->update(['locale' => $locale]);

        return redirect()->back();
    }
}
