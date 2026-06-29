<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Seed authors from existing owner values
        $owners = DB::table('mods')->distinct()->pluck('owner');
        foreach ($owners as $owner) {
            if ($owner !== null) {
                DB::table('authors')->insert([
                    'name' => $owner,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('mods', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });

        // Migrate data
        DB::statement('
            UPDATE mods
            SET author_id = (
                SELECT id FROM authors WHERE authors.name = mods.owner
            )
        ');

        Schema::table('mods', function (Blueprint $table) {
            $table->dropColumn('owner');
        });
    }

    public function down(): void
    {
        Schema::table('mods', function (Blueprint $table) {
            $table->string('owner')->index()->after('name');
        });

        DB::statement('
            UPDATE mods
            SET owner = (
                SELECT name FROM authors WHERE authors.id = mods.author_id
            )
        ');

        Schema::table('mods', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });
    }
};
