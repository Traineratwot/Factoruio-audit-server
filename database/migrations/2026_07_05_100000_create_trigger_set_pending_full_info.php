<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION set_pending_full_info()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.latest_version IS DISTINCT FROM OLD.latest_version THEN
                    NEW.pending_full_info := true;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_set_pending_full_info
            BEFORE UPDATE ON mods
            FOR EACH ROW
            EXECUTE FUNCTION set_pending_full_info();
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS trg_set_pending_full_info ON mods');
        DB::statement('DROP FUNCTION IF EXISTS set_pending_full_info()');
    }
};
