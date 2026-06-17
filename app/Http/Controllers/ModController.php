<?php

namespace App\Http\Controllers;

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

        $categoryALl = Mod::query()->whereHas('reports')->distinct()->pluck('category');

        // Строим фильтр для Meilisearch
        $whereClauses = [];
        $bindings = [];

        // Включаемые категории
        if (!empty($categoryInclude)) {
            $includeClauses = [];
            foreach ($categoryInclude as $cat) {
                if ($cat === 'null') {
                    $includeClauses[] = 'category IS NULL';
                } else {
                    $includeClauses[] = 'category = ?';
                    $bindings[] = $cat;
                }
            }
            if (!empty($includeClauses)) {
                $whereClauses[] = '(' . implode(' OR ', $includeClauses) . ')';
            }
        }

        // Исключающие категории
        if (!empty($categoryExclude)) {
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
            $find = Mod::search($search);
            $query = Mod::query()->with('reports')->whereHas('reports')->whereIn('id', collect($find)->pluck('id'));
        } else {
            $query = Mod::query()->with('reports')->whereHas('reports');
        }

        if (!empty($whereClauses)) {
            $query->whereRaw(implode(' AND ', $whereClauses), $bindings);
        }

        // Сортировка
        $acceptableSortFields = ['name', 'title', 'owner', 'category', 'downloads_count', 'popularity', 'score', 'latest_version'];
        if (!in_array($sortField, $acceptableSortFields)) {
            $sortField = 'created_at';
        }

        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($sortField === 'score') {
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

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        $mods = Mod::search($query)->paginate($perPage, ['*'], 'page', $page);

        return response()->json($mods);
    }
}
