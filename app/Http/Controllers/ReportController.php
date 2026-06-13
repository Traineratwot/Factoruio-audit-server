<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $mod = $request->route()->parameter('mod');
        $version = $request->route()->parameter('version');

        $report = Report::where('mod_name', $mod)->where('mod_version', $version)->firstOrFail();
        return Inertia::render('report', [
            'report' => $report,
        ]);
    }
}
