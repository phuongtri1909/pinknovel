<?php

use App\Models\Guide;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
        });

        Guide::all()->each(function ($guide) {
            $guide->slug = Str::slug($guide->title);
            $guide->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
