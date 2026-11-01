<?php

use App\Services\RaceCapacityService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (RaceCapacityService::RACES as $race) {
            DB::table('settings')->updateOrInsert(
                ['key' => $race['capacity_key']],
                ['value' => '0', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', array_column(RaceCapacityService::RACES, 'capacity_key'))
            ->delete();
    }
};
