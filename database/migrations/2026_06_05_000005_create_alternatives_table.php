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
        Schema::create('alternatives', function (Blueprint $table) {
            $table->id();
            $table->string('paid_tool')->unique();
            $table->string('open_tool');
            $table->string('category');
            $table->unsignedInteger('github_stars')->default(0);
            $table->timestamp('last_commit')->nullable();
            $table->decimal('monthly_cost_paid', 8, 2)->default(0);
            $table->boolean('docker_support')->default(false);
            $table->string('php_version_req')->nullable();
            $table->boolean('laravel_compatible')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('laravel_compatible');
            $table->index('github_stars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alternatives');
    }
};
