<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mod_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mod_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->string('file_name');
            $table->string('download_url');
            $table->string('sha1', 40);
            $table->string('factorio_version');
            $table->json('dependencies')->nullable();
            $table->timestamp('released_at');
            $table->timestamps();

            $table->unique(['mod_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mod_versions');
    }
};
