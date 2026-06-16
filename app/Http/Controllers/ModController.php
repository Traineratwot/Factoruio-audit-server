<?php

namespace App\Http\Controllers;

use App\Models\Mod;
use App\Http\Resources\ModResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $mods = Mod::query()
            ->with('reports')
            ->whereHas('reports')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('owner', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('welcome', [
            'mods' => ModResource::collection($mods),
            'search' => $search,
        ]);
    }

    // Метод report без изменений
    public function report(Request $request)
    {
        $mod_name = $request->route()->parameter('mod');
        $version = $request->route()->parameter('version');
        $mod = Mod::where('name', $mod_name)->firstOrFail();
        if (!$version) {
            $version = $mod->latest_version;
        }
        $report = $mod->reports()->where('mod_version', $version)->firstOrFail();
        return Inertia::render('report', [
            'report' => $report,
        ]);
    }
}
