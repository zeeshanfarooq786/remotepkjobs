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
        Schema::table('alternatives', function (Blueprint $table) {
            $table->unsignedInteger('open_issues')->default(0)->after('github_forks');
            $table->string('language')->nullable()->after('open_issues');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alternatives', function (Blueprint $table) {
            $table->dropColumn(['open_issues', 'language']);
        });
    }
};
