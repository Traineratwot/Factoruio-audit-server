<?php

namespace App\Http\Controllers;

use App\Jobs\AuditJob;
use App\Models\Mod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuditController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        $perPage = min((int) $request->input('per_page', 15), 50);

        $mods = Mod::query()
            ->where('name', 'like', '%'.$query.'%')
            ->orWhere('title', 'like', '%'.$query.'%')
            ->select('id', 'name', 'title')
            ->limit($perPage)
            ->get();

        return response()->json(['data' => $mods]);
    }

    public function versions(Mod $mod): JsonResponse
    {
        $versions = $mod->versions()
            ->select('id', 'version', 'factorio_version', 'released_at')
            ->orderByDesc('released_at')
            ->get();

        if ($versions->isEmpty()) {
            $mod->fetchFullInfo();
            $versions = $mod->versions()
                ->select('id', 'version', 'factorio_version', 'released_at')
                ->orderByDesc('released_at')
                ->get();
        }

        return response()->json([
            'versions' => $versions,
            'latest_version' => $mod->latest_version,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'mod_id' => 'required|exists:mods,id',
            'mod_version' => 'nullable|string',
        ]);

        /** @var Mod $mod */
        $mod = Mod::query()->findOrFail($request->input('mod_id'));
        $version = $request->input('mod_version') ?? $mod->latest_version;

        $ip = $request->ip();
        $isAdmin = $request->user() !== null;

        if (! $isAdmin) {
            $key = 'audit:'.$ip;
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'rate_limit' => "Rate limit exceeded. Try again in {$seconds} seconds.",
                ]);
            }
            RateLimiter::hit($key, 600);
        }

        $auditToken = $request->session()->get('audit_token');

        AuditJob::dispatch($mod->id, $version, $auditToken);

        return response()->json([
            'success' => true,
            'message' => 'Audit queued for '.$mod->name.' v'.$version,
        ]);
    }
}
