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
        Schema::create('salary_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('stack');
            $table->string('country');
            $table->unsignedInteger('avg_salary');
            $table->unsignedInteger('sample_size');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('stack');
            $table->index('country');
            $table->index('recorded_at');
            $table->index(['stack', 'country']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_snapshots');
    }
};
