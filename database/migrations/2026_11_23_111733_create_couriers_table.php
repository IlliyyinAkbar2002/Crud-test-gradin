<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('vehicle_type', 20);
            $table->string('license_plate', 15)->nullable();
            $table->unsignedTinyInteger('level')->default(1); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('level');
        });

        DB::statement('ALTER TABLE couriers ADD CONSTRAINT couriers_level_check CHECK (level BETWEEN 1 AND 5)');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
