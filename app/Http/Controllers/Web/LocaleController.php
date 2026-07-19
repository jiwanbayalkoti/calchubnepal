<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported = (array) config('calculator_hub.locales', ['en', 'ne']);

        if (! in_array($locale, $supported, true)) {
            return redirect()->to(url()->previous() ?: route('home'));
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        $fallback = route('home');
        $previous = url()->previous();
        $target = ($previous && $previous !== url()->current()) ? $previous : $fallback;

        return redirect()
            ->to($target)
            ->with('status', $locale === 'ne' ? 'भाषा नेपालीमा सेट भयो।' : 'Language set to English.');
    }
}
