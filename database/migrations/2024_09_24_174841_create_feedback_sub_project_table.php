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
        Schema::create('feedback_sub_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_project_id')->constrained()->onDelete('cascade');
        });
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['sub_project_id']);
            $table->dropColumn('sub_project_id');
            $table->boolean('no_validation')->default(false)->after('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->foreignId('sub_project_id')->constrained()->onDelete('cascade');
            $table->dropColumn('no_validation');
        });
        Schema::dropIfExists('feedback_sub_project');
    }
};
