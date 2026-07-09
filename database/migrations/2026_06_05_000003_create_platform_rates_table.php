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
        Schema::create('platform_rates', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('fee_type');
            $table->decimal('fee_value', 10, 4);
            $table->string('currency', 3)->default('USD');
            $table->date('effective_date');
            $table->timestamps();

            $table->index('platform');
            $table->index('fee_type');
            $table->index('effective_date');
            $table->unique(['platform', 'fee_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_rates');
    }
};
