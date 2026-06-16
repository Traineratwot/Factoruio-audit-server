<?php

namespace App\Http\Controllers;

use App\Http\Resources\ModResource;
use App\Http\Resources\ReportResource;
use App\Models\Mod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $categoryInclude = $request->input('category_include', []);
        $categoryExclude = $request->input('category_exclude', []);
        $categoryALl = Mod::query()->whereHas('reports')->distinct(['category'])->pluck("category");
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
            ->when(!empty($categoryInclude), function ($query) use ($categoryInclude) {
                $query->where(function ($q) use ($categoryInclude) {
                    foreach ($categoryInclude as $cat) {
                        if ($cat === 'null') {
                            $q->orWhereNull('category');
                        } else {
                            $q->orWhere('category', $cat);
                        }
                    }
                });
            })
            ->when(!empty($categoryExclude), function ($query) use ($categoryExclude) {
                $query->where(function ($q) use ($categoryExclude) {
                    foreach ($categoryExclude as $cat) {
                        if ($cat === 'null') {
                            $q->whereNotNull('category');
                        } else {
                            $q->where('category', '!=', $cat);
                        }
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('welcome', [
            'mods' => ModResource::collection($mods),
            'search' => $search,
            'category_include' => $categoryInclude,
            'category_exclude' => $categoryExclude,
            'category_all' => $categoryALl,
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
