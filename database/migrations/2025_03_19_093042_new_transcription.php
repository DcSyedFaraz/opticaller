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
        Schema::create('newtranscripts', function (Blueprint $table) {
            $table->id();
            $table->string('transcript_sid')->unique();
            $table->string('recording_sid')->unique();
            $table->string('call_sid')->nullable();
            $table->string('status');
            $table->text('transcription_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newtranscripts');
    }
};
