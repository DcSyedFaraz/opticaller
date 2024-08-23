<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_locked_fields', function (Blueprint $table) {
            $table->id();
            $table->json('locked_fields')->nullable();
            $table->timestamps();
        });

        // Insert an initial record with empty locked fields
        DB::table('global_locked_fields')->insert([
            'locked_fields' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_locked_fields');
    }
};
