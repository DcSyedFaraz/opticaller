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
        // Remove all existing rows from the table.
        DB::table('transcriptions')->truncate();

        Schema::table('transcriptions', function (Blueprint $table) {

            // Add new columns
            $table->string('callerIdentity')->nullable()->after('transcription_sid');
            $table->string('call_sid')->nullable()->after('recording_sid');
            // status is required so we set a default (adjust as needed)
            $table->string('status')->default('pending')->after('call_sid');
        });
    }

    public function down()
    {
        Schema::table('transcriptions', function (Blueprint $table) {
            // Revert changes: rename column back and drop the added columns
            $table->dropColumn('callerIdentity');
            $table->dropColumn('call_sid');
            $table->dropColumn('status');
        });
    }
};
