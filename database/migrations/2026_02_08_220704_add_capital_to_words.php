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
        Schema::create('words_capital', function (Blueprint $table) {
            $table->id();
            $table->string('Name');
        });

        Schema::table('words', function (Blueprint $table) {
            $table->foreignId('words_capital_id')->nullable()->constrained('words_capital')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->dropForeignIdFor('words_capital');
            $table->dropColumn('words_capital_id');
        });
        
        Schema::dropIfExists('words_capital');
    }
};
