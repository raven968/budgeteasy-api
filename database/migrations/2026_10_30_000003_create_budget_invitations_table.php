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
        Schema::create('budget_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->timestamp('expires_at');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_invitations');
    }
};
