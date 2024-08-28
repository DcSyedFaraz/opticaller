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
        Schema::create('callback_mails', function (Blueprint $table) {
            $table->id();
            $table->string('project');
            $table->string('salutation');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number', 20);
            $table->text('notes');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callback_mails');
    }
};
