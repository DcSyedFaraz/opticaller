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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('salutation')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('street_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_address_system')->unique();
            $table->string('email_address_new')->nullable();
            // $table->text('personal_notes')->nullable();
            $table->text('interest_notes')->nullable();
            $table->string('feedback')->nullable();
            $table->string('follow_up_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('priority')->default(0);
            $table->integer('seen')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
