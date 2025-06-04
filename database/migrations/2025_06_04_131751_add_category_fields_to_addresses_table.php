<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('main_category_query')->nullable()->after('titel');
            $table->string('sub_category_category')->nullable()->after('main_category_query');
            $table->boolean('forbidden_promotion')->default(false)->after('sub_category_category');
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['main_category_query', 'sub_category_category', 'forbidden_promotion']);
        });
    }
};
