<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('icon', 50);
            $table->char('color', 7);
            $table->timestamps();
            $table->softDeletes();
        });

        // Active category names are unique per user, case-insensitive; deleted ones free the name.
        DB::statement('CREATE UNIQUE INDEX categories_user_id_name_unique ON categories (user_id, lower(name)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
