<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (! $request->session()->has('audit_token')) {
            $request->session()->put('audit_token', (string) Str::uuid());
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'app_url' => rtrim(config('app.url'), '/'),
            'auth' => [
                'user' => $request->user(),
            ],
            'audit_token' => $request->session()->get('audit_token'),
        ];
    }
}
