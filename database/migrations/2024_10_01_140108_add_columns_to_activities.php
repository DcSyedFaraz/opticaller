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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('feedback')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('sub_project_id')->nullable();
            $table->dropForeign(['user_id']);
            $table->dropForeign(['address_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('feedback');
            $table->dropColumn('contact_id');
            $table->dropColumn('sub_project_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');

        });
    }
};
