<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alternatives', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('paid_tool');
            $table->unsignedInteger('github_forks')->default(0)->after('github_stars');
            $table->json('comparison_json')->nullable()->after('description');
        });

        $rows = DB::table('alternatives')->orderBy('id')->get();

        foreach ($rows as $row) {
            DB::table('alternatives')->where('id', $row->id)->update([
                'slug' => Str::slug($row->paid_tool.'-alternative'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alternatives', function (Blueprint $table) {
            $table->dropColumn(['slug', 'github_forks', 'comparison_json']);
        });
    }
};
