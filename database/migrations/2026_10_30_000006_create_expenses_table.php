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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->bigInteger('amount');
            $table->date('spent_on');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['budget_id', 'spent_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
