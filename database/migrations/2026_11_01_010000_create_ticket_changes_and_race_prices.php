<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('from_value');
            $table->string('to_value');
            $table->decimal('amount_difference', 8, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        $defaultPrice = (string) (DB::table('settings')->where('key', 'ticket_price')->value('value') ?? 28);

        foreach ([
            'race_price_trail_46k',
            'race_price_sky_27k',
            'race_price_downhill_12k',
            'race_price_santa_felicita_14k',
        ] as $key) {
            DB::table('settings')->updateOrInsert(['key' => $key], [
                'value' => $defaultPrice,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'race_price_trail_46k',
            'race_price_sky_27k',
            'race_price_downhill_12k',
            'race_price_santa_felicita_14k',
        ])->delete();

        Schema::dropIfExists('ticket_changes');
    }
};
