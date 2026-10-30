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
        Schema::create('budget_category_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->index()->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->timestamps();

            $table->unique(['budget_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_category_limits');
    }
};
