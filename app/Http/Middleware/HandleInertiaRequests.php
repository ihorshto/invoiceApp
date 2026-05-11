<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        $translations = [];
        $langPath = lang_path($locale);
        if (is_dir($langPath)) {
            foreach (glob($langPath . '/*.php') as $file) {
                $group = basename($file, '.php');
                $translations[$group] = trans($group);
            }
        }

        return [
            ...parent::share($request),
            'auth'         => ['user' => $request->user()],
            'locale'       => $locale,
            'translations' => $translations,
        ];
    }
}
