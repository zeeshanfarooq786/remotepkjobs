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
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('company');
        });

        $jobs = DB::table('jobs')->orderBy('id')->get();

        foreach ($jobs as $job) {
            $base = Str::slug($job->title.'-'.$job->company);
            $slug = $base;
            $counter = 1;

            while (
                DB::table('jobs')
                    ->where('slug', $slug)
                    ->where('id', '!=', $job->id)
                    ->exists()
            ) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            DB::table('jobs')->where('id', $job->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
