<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application language
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLang(Request $request, $locale)
    {
        // Check if the locale is supported
        if (!in_array($locale, config('app.supported_locales'))) {
            $locale = config('app.fallback_locale');
        }

        // Store the locale in session
        Session::put('locale', $locale);
        App::setLocale($locale);

        return redirect()->back();
    }
}
