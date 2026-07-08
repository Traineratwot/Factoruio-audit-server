<?php

namespace App\Http\Controllers;

use App\Facades\AuditService;
use App\Http\Resources\ModResource;
use App\Models\Mod;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $categoryInclude = $request->input('category_include', []);
        $categoryExclude = $request->input('category_exclude', []);
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $reportFilter = $request->input('report_filter', 'all');
        $factorioVersion = $request->input('factorio_version', '');

        $categoryALl = Mod::query()->distinct()->pluck('category');
        $factorioVersions = Mod::query()
            ->whereNotNull('factorio_version')
            ->distinct()
            ->orderByDesc('factorio_version')
            ->pluck('factorio_version');

        // Строим фильтр для Meilisearch
        $whereClauses = [];
        $bindings = [];

        // Включаемые категории
        if (! empty($categoryInclude)) {
            $includeClauses = [];
            foreach ($categoryInclude as $cat) {
                if ($cat === 'null') {
                    $includeClauses[] = 'category IS NULL';
                } else {
                    $includeClauses[] = 'category = ?';
                    $bindings[] = $cat;
                }
            }
            if (! empty($includeClauses)) {
                $whereClauses[] = '('.implode(' OR ', $includeClauses).')';
            }
        }

        // Исключающие категории
        if (! empty($categoryExclude)) {
            foreach ($categoryExclude as $cat) {
                if ($cat === 'null') {
                    $whereClauses[] = 'category IS NOT NULL';
                } else {
                    $whereClauses[] = 'category != ?';
                    $bindings[] = $cat;
                }
            }
        }

        if ($search) {
            $find = Mod::search($search)->get()->pluck('id')->toArray();
            $query = Mod::query()->with(['reports', 'author'])->whereIn('id', $find);
            if (! empty($find)) {
                $query->orderByRaw('array_position(array['.implode(',', $find).']::bigint[], id)');
            }
        } else {
            $query = Mod::query()->with(['reports', 'author']);
        }

        if ($reportFilter === 'with') {
            $query->whereHas('reports');
        } elseif ($reportFilter === 'without') {
            $query->whereDoesntHave('reports');
        }

        if ($factorioVersion) {
            $query->where('factorio_version', $factorioVersion);
        }

        if (! empty($whereClauses)) {
            $query->whereRaw(implode(' AND ', $whereClauses), $bindings);
        }

        // Сортировка
        $acceptableSortFields = ['name', 'title', 'owner', 'category', 'downloads_count', 'popularity', 'score', 'latest_version'];
        if (! in_array($sortField, $acceptableSortFields)) {
            $sortField = 'popularity';
            $direction = 'desc';
        }

        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($sortField === 'owner') {
            $query->join('authors', 'authors.id', '=', 'mods.author_id')
                ->orderBy('authors.name', $direction);
        } elseif ($sortField === 'score') {
            $query->orderBy(
                Report::select('score')
                    ->whereColumn('reports.mod_id', 'mods.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1),
                $direction
            );
        } else {
            $query->orderBy($sortField, $direction);
        }

        $mods = $query->paginate(10)->withQueryString();

        return Inertia::render('welcome', [
            'mods' => ModResource::collection($mods),
            'search' => $search,
            'category_include' => $categoryInclude,
            'category_exclude' => $categoryExclude,
            'category_all' => $categoryALl,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
            'report_filter' => $reportFilter,
            'factorio_version' => $factorioVersion,
            'factorio_versions' => $factorioVersions,
        ]);
    }

    // Метод report без изменений
    public function report(Request $request)
    {
        $mod_name = $request->route()->parameter('mod');
        $version = $request->route()->parameter('version');
        $mod = Mod::where('name', $mod_name)->firstOrFail();

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

        if (! $version) {
            $version = $mod->latest_report_version ?? $mod->latest_version;
        }

        $report = $mod->reports()->where('mod_version', $version)->first();
        $reportedVersions = $mod->reports()->pluck('mod_version')->toArray();

        return Inertia::render('report', [
            'report' => $report,
            'mod' => [
                'id' => $mod->id,
                'name' => $mod->name,
                'title' => $mod->title,
                'image' => $mod->getImage(),
                'summary' => $mod->summary,
                'category' => $mod->category,
            ],
            'versions' => $versions,
            'current_version' => $version,
            'latest_version' => $mod->latest_version,
            'reported_versions' => $reportedVersions,
            'current_scanner_version' => AuditService::cachedScannerVersion(),
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        $mods = Mod::search($query)->paginate($perPage, 'page', $page);

        return response()->json($mods);
    }
}
