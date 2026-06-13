<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('mod_name');
            $table->string('mod_version');

            $table->float('score');

            $table->jsonb('raw');
            $table->char('sha1', 40)->unique();

            $table->unique(['mod_name', 'mod_version']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
