<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('scanner_version')->nullable()->after('score');
        });

        DB::table('reports')
            ->whereNull('scanner_version')
            ->update(['scanner_version' => DB::raw("raw->'report'->>'scannerVersion'")]);
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('scanner_version');
        });
    }
};
