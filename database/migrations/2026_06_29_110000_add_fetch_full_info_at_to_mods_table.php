<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->timestamp('fetch_full_info_at')->nullable()->after('latest_release_date');
        });
    }

    public function down(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->dropColumn('fetch_full_info_at');
        });
    }
};
